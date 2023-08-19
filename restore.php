<?php
require_once('connect.php');  // Assuming you have a connect.php to establish database connection

if (isset($_POST['date']) && $_POST['confirm'] == 'yes') {
    $date = $_POST['date'];

    // Fetch all rows from stock_log for the selected date
    $stmt = $con->prepare("SELECT article_id, original_stock FROM stock_log WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $article_id = $row['article_id'];
        $original_stock = $row['original_stock'];

        // Update the current stock in article_info
        $stmt = $con->prepare("UPDATE article_info SET stock = ? WHERE article_id = ?");
        $stmt->bind_param("ii", $original_stock, $article_id);
        $stmt->execute();

        echo "Stock restored successfully for Article ID: $article_id on Date: $date <br>";
    }
} else if (isset($_POST['date']) && $_POST['confirm'] == 'no') {
    header("Location: index.php");  // Redirect to some other page
} else {
    echo "No Date provided";
}

// Fetch all distinct dates from stock_log for the dropdown
$stmt = $con->prepare("SELECT DISTINCT date FROM stock_log ORDER BY date");
$stmt->execute();
$result = $stmt->get_result();
$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
}
?>
<form method="POST" action="">
    <label for="date">Date:</label>
    <select id="date" name="date">
        <?php foreach ($dates as $date): ?>
            <option value="<?php echo $date; ?>"><?php echo $date; ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Show Values">
</form>

<?php
if (isset($_POST['date']) && !isset($_POST['confirm'])) {
    $date = $_POST['date'];
    echo "<h3>Current and Restored Values for Date: $date</h3>";
    echo "<table>
            <tr>
                <th>Article ID</th>
                <th>Current Stock</th>
                <th>Restored Stock</th>
            </tr>";

    $stmt = $con->prepare("SELECT article_info.article_id, article_info.stock, stock_log.original_stock
                           FROM article_info INNER JOIN stock_log ON article_info.article_id = stock_log.article_id
                           WHERE stock_log.date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['article_id']}</td>
                <td>{$row['stock']}</td>
                <td>{$row['original_stock']}</td>
              </tr>";
    }

    echo "</table>";
    echo "<form method='POST' action=''>
            <input type='hidden' name='date' value='$date'>
            <input type='submit' name='confirm' value='yes'>
            <input type='submit' name='confirm' value='no'>
          </form>";
}
?>
