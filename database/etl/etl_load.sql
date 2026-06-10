
-- ETL script to populate `food_dw` from `food_ordering_system` (MySQL)
-- This version adds basic logging, batching, and error handling via a stored procedure.
-- Usage:
--   mysql -u root -p < dw_schema.sql      # ensure DW schema exists
--   mysql -u root -p < etl_load.sql       # run ETL (creates an ETL run log and runs in batches)

-- 0) Create ETL run log table
CREATE TABLE IF NOT EXISTS food_dw.etl_run_log (
    run_id INT AUTO_INCREMENT PRIMARY KEY,
    started_at DATETIME,
    finished_at DATETIME,
    status VARCHAR(20),
    rows_loaded INT DEFAULT 0,
    error_message TEXT
);

-- 1) Populate dimensions (idempotent/upsert)
INSERT IGNORE INTO food_dw.dim_date (date, year_small, month_small, day_small, quarter_small, weekday_name)
SELECT
    DATE(o.created_at) AS date,
    YEAR(o.created_at) AS year_small,
    MONTH(o.created_at) AS month_small,
    DAY(o.created_at) AS day_small,
    QUARTER(o.created_at) AS quarter_small,
    DAYNAME(o.created_at) AS weekday_name
FROM food_ordering_system.orders o
GROUP BY DATE(o.created_at);

INSERT INTO food_dw.dim_user (user_id, username, email, city, state, country)
SELECT u.id AS user_id, u.username, u.email, u.city, u.state, u.country
FROM food_ordering_system.users u
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    email = VALUES(email),
    city = VALUES(city),
    state = VALUES(state),
    country = VALUES(country);

INSERT INTO food_dw.dim_category (category_id, name, description)
SELECT c.id AS category_id, c.name, c.description
FROM food_ordering_system.categories c
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);

INSERT INTO food_dw.dim_product (food_id, name, category_sk, price, is_vegetarian, is_available)
SELECT f.id AS food_id, f.name, dc.category_sk, f.price, f.is_vegetarian, f.is_available
FROM food_ordering_system.food_items f
LEFT JOIN food_dw.dim_category dc ON dc.category_id = f.category_id
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category_sk = VALUES(category_sk),
    price = VALUES(price),
    is_vegetarian = VALUES(is_vegetarian),
    is_available = VALUES(is_available);

-- 2) Define stored procedure for batched fact loads
DELIMITER $$
DROP PROCEDURE IF EXISTS food_dw.etl_load_proc$$
CREATE PROCEDURE food_dw.etl_load_proc()
BEGIN
    DECLARE batch_size INT DEFAULT 1000;
    DECLARE last_order_id INT DEFAULT 0;
    DECLARE max_order_id INT DEFAULT 0;
    DECLARE rows_this_batch INT DEFAULT 0;
    DECLARE total_rows INT DEFAULT 0;
    DECLARE current_run_id INT DEFAULT 0;
    DECLARE done INT DEFAULT 0;
    DECLARE upper_bound INT DEFAULT 0;

    -- Error handler: rollback and mark run failed
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        UPDATE food_dw.etl_run_log SET status='failed', finished_at=NOW(), rows_loaded=total_rows, error_message='SQLEXCEPTION during ETL' WHERE run_id=current_run_id;
        SET done = 1;
    END;

    -- create run log
    INSERT INTO food_dw.etl_run_log (started_at, status, rows_loaded) VALUES (NOW(),'running',0);
    SET current_run_id = LAST_INSERT_ID();

    SET last_order_id = IFNULL((SELECT MAX(order_id) FROM food_dw.fact_order_items),0);
    SET max_order_id = IFNULL((SELECT MAX(id) FROM food_ordering_system.orders),0);

    WHILE last_order_id < max_order_id DO
        SET upper_bound = last_order_id + batch_size;
        START TRANSACTION;

        INSERT INTO food_dw.fact_order_items (order_id, order_number, order_date_id, user_sk, product_sk, quantity, price, subtotal, payment_status, order_status, created_at)
        SELECT
            o.id,
            o.order_number,
            dd.date_id,
            du.user_sk,
            dp.product_sk,
            oi.quantity,
            oi.price,
            oi.subtotal,
            o.payment_status,
            o.order_status,
            o.created_at
        FROM food_ordering_system.orders o
        JOIN food_ordering_system.order_items oi ON oi.order_id = o.id
        JOIN food_dw.dim_date dd ON dd.date = DATE(o.created_at)
        JOIN food_dw.dim_user du ON du.user_id = o.user_id
        JOIN food_dw.dim_product dp ON dp.food_id = oi.food_id
        LEFT JOIN food_dw.fact_order_items f ON f.order_id = o.id AND f.product_sk = dp.product_sk
        WHERE o.id > last_order_id AND o.id <= @upper AND f.fact_id IS NULL;

        SET rows_this_batch = ROW_COUNT();
        COMMIT;

        SET total_rows = total_rows + rows_this_batch;
        UPDATE food_dw.etl_run_log SET rows_loaded = total_rows WHERE run_id = current_run_id;

        -- advance
        SET last_order_id = upper_bound;

        -- safety: if handler triggered, exit loop by advancing to max
        IF done = 1 THEN
            SET last_order_id = max_order_id;
        END IF;
    END WHILE;

    -- finalize
    UPDATE food_dw.etl_run_log SET status='completed', finished_at=NOW(), rows_loaded=total_rows WHERE run_id = current_run_id;
END$$
DELIMITER ;

-- 3) Execute the procedure and then drop it (keeps schema tidy)
CALL food_dw.etl_load_proc();
DROP PROCEDURE IF EXISTS food_dw.etl_load_proc;

-- End ETL
