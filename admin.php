<?php include_once 'admin_header.php'; ?>

<?php 

$stmt_below_threshold = $con->prepare("SELECT article_name, stock, threshold FROM article_info WHERE stock <= threshold ORDER BY stock ASC");
$stmt_below_threshold->execute();
$result_below_threshold = $stmt_below_threshold->get_result();

// Fetching data for Recent Activities
$stmt_recent_activities = $con->prepare("SELECT h.type, h.time, h.original_value, h.updated_value, a.article_name FROM history h JOIN article_info a ON h.article_id = a.article_id ORDER BY h.time DESC LIMIT 5");
$stmt_recent_activities->execute();
$result_recent_activities = $stmt_recent_activities->get_result();


// Fetching data for API Monitoring
$stmt_api_requests = $con->prepare("SELECT COUNT(id) as total_requests FROM api_requests");
$stmt_api_requests->execute();
$result_api_requests = $stmt_api_requests->get_result();
$data_api_requests = $result_api_requests->fetch_assoc();
;


$stmt_recent_api = $con->prepare("SELECT * FROM api_requests ORDER BY timestamp DESC LIMIT 5");
$stmt_recent_api->execute();
$result_recent_api = $stmt_recent_api->get_result();


// Fetching data for Error Logs
$stmt_error_logs = $con->prepare("SELECT * FROM error_log ORDER BY timestamp DESC LIMIT 5");
$stmt_error_logs->execute();
$result_error_logs = $stmt_error_logs->get_result();

?>

<script>
$(document).ready(function() {
    $(".extra-rows").hide();

    $("#loadMore").click(function() {
        $(".extra-rows").fadeIn();
        $(this).hide();
    });
});
</script>

<h2>管理画面へ</h2>
こちらは管理画面です。


<!-- Displaying Stock Overview -->
<div class="card">
    <div class="card-header">
        Stock Overview
    </div>
    <div class="card-body">
        <h5 class="card-title">Articles Below Threshold</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Article Name</th>
                    <th>Stock</th>
                    <th>Threshold</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
                <?php while($row = $result_below_threshold->fetch_assoc()): ?>
                    <tr class="<?php echo ($count >= 5) ? 'extra-rows' : ''; ?>">
                        <td><?php echo $row['article_name']; ?></td>
                        <td><?php echo $row['stock']; ?></td>
                        <td><?php echo $row['threshold']; ?></td>
                    </tr>
                    <?php $count++; ?>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php if($count > 5): ?>
            <button id="loadMore" class="btn btn-primary">Load More</button>
        <?php endif; ?>
    </div>
</div>



<!-- Displaying Recent Activities -->
<div class="card">
    <div class="card-header">
        Recent Activities
    </div>
    <div class="card-body">
        <h5 class="card-title">Last 5 Activities</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Article Name</th>
                    <th>Type</th>
                    <th>Original Value</th>
                    <th>Updated Value</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result_recent_activities->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['article_name']; ?></td>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo $row['original_value']; ?></td>
                        <td><?php echo $row['updated_value']; ?></td>
                        <td><?php echo $row['time']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Displaying API Monitoring -->
<div class="card">
    <div class="card-header">API Monitoring</div>
    <div class="card-body">
        <p>Total API Requests: <?= $data_api_requests['total_requests'] ?></p>
        <h5>Recent API Requests:</h5>
        <ul>
            <?php while($row = mysqli_fetch_assoc($result_recent_api)): ?>
                <li><?= $row['method'] ?> - <?= $row['url'] ?> (<?= $row['timestamp'] ?>)</li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<!-- Displaying Error Logs -->
<div class="card">
    <div class="card-header">Error Logs</div>
    <div class="card-body">
        <ul>
            <?php while($row = mysqli_fetch_assoc($result_error_logs)): ?>
                <li><?= $row['error_message'] ?> (<?= $row['timestamp'] ?>)</li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>



<?php include_once 'admin_footer.php'; ?>