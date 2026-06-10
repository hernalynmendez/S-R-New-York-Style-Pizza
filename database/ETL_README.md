# Data Warehouse ETL

This folder contains a minimal star schema and an example ETL script to populate
the warehouse from the application database `food_ordering_system`.

Files:
- `dw_schema.sql` — creates `food_dw` and the dimension/fact tables.
- `etl/etl_load.sql` — idempotent-ish ETL script: populates dims and inserts new facts.

How to run (on the same MySQL server):

1. Create the DW schema:

```sh
mysql -u root -p < database/dw_schema.sql
```

2. Run ETL:

```sh
mysql -u root -p < database/etl/etl_load.sql
```

3. Optional: use the PHP CLI helper to run ETL and view logs (reads credentials from `.env`):

```sh
php scripts/run_etl.php database/etl/etl_load.sql
```

4. Optional: view historical ETL runs in the admin UI at `admin/etl_logs.php` (requires admin login).

Notes:
- The script assumes both `food_ordering_system` and `food_dw` are on the same MySQL instance.
- Dims use unique natural keys (source IDs) and are upserted; facts are appended while avoiding duplicate order_id/product combos.
- For production use: add logging, error handling, batching, and a scheduler (cron/Task Scheduler) or a dedicated ETL tool (Airflow, dbt).

Example cron (run hourly, Linux):

```cron
0 * * * * mysql -u root -pYourPass < /path/to/FoodSystem/database/etl/etl_load.sql
```
