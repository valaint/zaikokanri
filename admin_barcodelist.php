<?php include_once 'admin_header.php'; ?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$subquery = "SELECT barcode FROM barcode_list GROUP BY barcode HAVING COUNT(*) > 1";

// Main query to get entries where the barcode appears in the subquery
$sql1 = "SELECT id, barcode, article_id, destock_count, is_prompt FROM barcode_list WHERE barcode IN (" . $subquery . ")";
$result1 = $con->query($sql1);

if ($con->error) {
    die("SQL error: " . $conn->error);
}

// Query for all articles
$sql2 = "SELECT article_id, article_name FROM article_info";
$result2 = $con->query($sql2);

$articles = [];
while ($article = $result2->fetch_assoc()) {
    $articles[$article["article_id"]] = $article["article_name"];
}

// Query for all other entries
$sql3 = "SELECT id, barcode, article_id, destock_count, is_prompt FROM barcode_list GROUP BY barcode HAVING COUNT(*) = 1";
$result3 = $con->query($sql3);

?>

<div class="container">
  <h3>セット品</h3>
  <table class="table table-bordered">
  <thead>
    <tr>
      <th>バーコード文字列</th>
      <th>品名</th>
      <th>出庫数</th>
      <th>即時メール</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $last_barcode = null;
    $barcode_counts = [];
    while($row = $result1->fetch_assoc()){
      $barcode_counts[$row['barcode']][] = $row;
    }
    foreach($barcode_counts as $barcode => $rows) {
      $count = count($rows);
      foreach($rows as $index => $row) {
        echo '<tr>';
        if ($index === 0) {
          echo '<td rowspan="'.$count.'"><input type="text" class="form-control" value="'.$barcode.'"></td>';
        }
        echo '
        <td>
          <select class="form-control">';
            foreach($articles as $id => $name) {
              $selected = $id == $row["article_id"] ? "selected" : "";
              echo '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
            }
        echo '
          </select>
        </td>
        <td><input type="text" class="form-control" value="'.$row["destock_count"].'"></td>
        <td><input type="checkbox" class="form-check-input" '.($row["is_prompt"] ? "checked" : "").'><input type="hidden" class="id-input" value="'.$row["id"].'"></td>
        <td><button class="btn btn-primary">Update</button><button class="btn btn-danger">Delete</button></td>
        </tr>';
      }
    }
    ?>
  </tbody>
</table>
  <h3>その他</h3>

  <table class="table table-bordered">
    <thead>
      <tr>
      <th>バーコード文字列</th>
      <th>品名</th>
      <th>出庫数</th>
      <th>即時メール</th>
      <th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result3->fetch_assoc()): ?>
      <tr>
        <td><input type="text" class="form-control" value="<?= $row["barcode"] ?>"></td>
        <td>
          <select class="form-control">
            <?php foreach($articles as $id => $name): ?>
              <option value="<?= $id ?>" <?= $id == $row["article_id"] ? "selected" : "" ?>>
                <?= $name ?>
              </option>
            <?php endforeach; ?>
          </select>
        </td>
        <td><input type="text" class="form-control" value="<?= $row["destock_count"] ?>"></td>
        <td><input type="checkbox" class="form-check-input" <?= $row["is_prompt"] ? "checked" : "" ?>><input type="hidden" class="id-input" value="<?= $row["id"] ?>"></td>
        <td><button class="btn btn-primary">更新</button><button class="btn btn-danger">削除</button></td>
      </tr>
      <?php endwhile; ?>
      <tr>
        <td><input type="text" class="form-control"></td>
        <td>
          <select class="form-control">
            <?php foreach($articles as $id => $name): ?>
              <option value="<?= $id ?>">
                <?= $name ?>
              </option>
            <?php endforeach; ?>
          </select>
        </td>
        <td><input type="text" class="form-control"></td>
        <td><input type="checkbox" class="form-check-input"></td>
        <td><button class="btn btn-success">追加</button></td>
      </tr>
    </tbody>
  </table>

</div>

<?php
$con->close();
include_once 'admin_footer.php';
?>

<script>
$(document).ready(function() {

    // Update function
    $(document).on('click', '.btn-primary', function() {
    var row = $(this).closest("tr");
    var id = row.find(".id-input").val(); // new line
    var barcode = row.find("td:nth-child(1)").find("input").val();
    var article_id = row.find("td:nth-child(2)").find("select").val();
    var destock_count = row.find("td:nth-child(3)").find("input").val();
    var is_prompt = row.find("td:nth-child(4)").find("input").is(':checked') ? 1 : 0;

    $.post("barcode_functions.php", { action: "update", id: id, barcode: barcode, article_id: article_id, destock_count: destock_count, is_prompt: is_prompt })
        .done(function(data) {
            console.log("Response:", data);
            alert("Updated successfully!");
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Error:", textStatus, errorThrown);
            alert("Error updating entry.");
        });
});

$(document).on('click', '.btn-danger', function() {
    var row = $(this).closest("tr");
    var id = row.find(".id-input").val(); // new line
    var barcode = row.find("td:nth-child(1)").find("input").val();

    $.post("barcode_functions.php", { action: "delete", id: id, barcode: barcode })
        .done(function() {
            row.remove();
            alert("Deleted successfully!");
        })
        .fail(function() {
            alert("Error deleting entry.");
        });
});

    // Add function
    $(document).on('click', '.btn-success', function() {
        var row = $(this).closest("tr");
        var barcode = row.find("td:nth-child(1)").find("input").val();
        var article_id = row.find("td:nth-child(2)").find("select").val();
        var destock_count = row.find("td:nth-child(3)").find("input").val();
        var is_prompt = row.find("td:nth-child(4)").find("input").is(':checked') ? 1 : 0;

        $.post("barcode_functions.php", { action: "add", barcode: barcode, article_id: article_id, destock_count: destock_count, is_prompt: is_prompt }, function(response) {
            // The response should contain the new barcode
            row.before('<tr><td><input type="text" class="form-control" value="' + response.barcode + '"></td><td><input type="text" class="form-control" value="' + article_id + '"></td><td><input type="text" class="form-control" value="' + destock_count + '"></td><td><input type="checkbox" class="form-check-input" ' + (is_prompt ? 'checked' : '') + '></td><td><button class="btn btn-primary">Update</button> <button class="btn btn-danger">Delete</button></td></tr>');
        }, "json")
        .done(function() {
            alert("Added successfully!");
        })
        .fail(function() {
            alert("Error adding entry.");
        });
    });
});
</script>