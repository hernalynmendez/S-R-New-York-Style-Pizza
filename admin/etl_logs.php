<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
checkSessionTimeout();

$res = $conn->query("SELECT run_id, started_at, finished_at, status, rows_loaded, error_message FROM food_dw.etl_run_log ORDER BY run_id DESC LIMIT 100");

require_once __DIR__ . '/../includes/header.php';
?>
<div class="card" style="margin:20px;padding:20px">
    <h3>ETL Run Log</h3>
    <p>Shows recent ETL runs for the `food_dw` warehouse.</p>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Run</th><th>Started</th><th>Finished</th><th>Status</th><th>Rows</th><th>Error (truncated)</th></tr>
            </thead>
            <tbody>
                <?php if ($res): while ($r = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['run_id']); ?></td>
                        <td><?php echo htmlspecialchars($r['started_at']); ?></td>
                        <td><?php echo htmlspecialchars($r['finished_at']); ?></td>
                        <td><?php echo htmlspecialchars($r['status']); ?></td>
                        <td><?php echo htmlspecialchars($r['rows_loaded']); ?></td>
                        <td style="max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($r['error_message']); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6">No ETL runs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
