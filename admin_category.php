<?php
include_once 'admin_header.php';
include('connect.php');

$sql = "SELECT category_id, category_name FROM category";
$result = $con->query($sql);

if ($con->error) {
    die("SQL error: " . $conn->error);
}
?>

<div class="container">
  <h3>Categories</h3>
  <table class="table table-bordered">
  <thead>
    <tr>
      <th>Category ID</th>
      <th>Category Name</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row["category_id"] ?></td>
        <td><input type="text" class="form-control" value="<?= $row["category_name"] ?>"></td>
        <td><button class="btn btn-primary">更新</button> <button class="btn btn-danger">削除</button></td>
      </tr>
    <?php endwhile; ?>
    <tr>
      <td></td>
      <td><input type="text" class="form-control"></td>
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
    var category_id = row.find("td:nth-child(1)").text();
    var category_name = row.find("td:nth-child(2)").find("input").val();

    $.post("category_functions.php", { action: "update", category_id: category_id, category_name: category_name })
        .done(function(data) {
            console.log("Response:", data);
            alert("Updated successfully!");
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Error:", textStatus, errorThrown);
            alert("Error updating entry.");
        });
    });

    // Delete function
    $(document).on('click', '.btn-danger', function() {
    var row = $(this).closest("tr");
    var category_id = row.find("td:nth-child(1)").text();

    $.post("category_functions.php", { action: "delete", category_id: category_id })
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
        var category_id = row.find("td:nth-child(1)").text();
        var category_name = row.find("td:nth-child(2)").find("input").val();

        $.post("category_functions.php", { action: "add", category_id: category_id, category_name: category_name }, function(response) {
            // The response should contain the new category_id
            row.before('<tr><td>' + response.category_id + '</td><td><input type="text" class="form-control" value="' + category_name + '"></td><td><button class="btn btn-primary">Update</button> <button class="btn btn-danger">Delete</button></td></tr>');
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
