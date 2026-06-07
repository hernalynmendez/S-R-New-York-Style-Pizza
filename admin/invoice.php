<?php
/**
 * Simple Invoice Print View
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

requireAdmin();

$order_id = isset($_GET['order']) ? (int)$_GET['order'] : 0;
if ($order_id <= 0) die('Invalid order');

$order = getOrderById($order_id);
if (!$order) die('Order not found');

$items = getOrderItems($order_id);

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Invoice - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/style.css">
    <style>
        body{background:#fff;color:#111;font-family:Inter,system-ui,Arial;margin:0;padding:20px}
        .invoice{max-width:800px;margin:0 auto;background:#fff;padding:24px;border-radius:8px}
        .invoice header{display:flex;justify-content:space-between;align-items:center}
        .invoice table{width:100%;border-collapse:collapse;margin-top:12px}
        .invoice table td, .invoice table th{padding:8px;border-bottom:1px solid #eee}
        .text-right{text-align:right}
        .muted{color:#666}
    </style>
</head>
<body>
    <div class="invoice">
        <header>
            <div>
                <h2>Pizza Brand</h2>
                <div class="muted">Invoice for Order <?php echo htmlspecialchars($order['order_number']); ?></div>
            </div>
            <div class="text-right">
                <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                <div class="muted"><?php echo formatDate($order['created_at']); ?></div>
            </div>
        </header>

        <section>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['food_name']); ?></td>
                            <td class="text-right"><?php echo $it['quantity']; ?></td>
                            <td class="text-right"><?php echo formatPrice($it['price']); ?></td>
                            <td class="text-right"><?php echo formatPrice($it['subtotal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Total</th>
                        <th class="text-right"><?php echo formatPrice($order['total_amount']); ?></th>
                    </tr>
                </tfoot>
            </table>
        </section>

        <div style="margin-top:18px;display:flex;justify-content:space-between;align-items:center">
            <div class="muted">Payment: <?php echo ucfirst($order['payment_status']); ?></div>
            <div>
                <button onclick="window.print();" class="btn btn-primary">Print</button>
            </div>
        </div>
    </div>
</body>
</html>
