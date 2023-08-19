<?php include_once 'admin_header.php'; ?>
<?php
include('connect.php');
include('contact_functions.php');

$result = $con->query("SELECT `contact_id`, `name`, `email` FROM `contact`");
?>

<div class="container">
    <table class="table table-striped" id="contacts-table">
        <thead>
            <tr>
                <th>Contact ID</th>
                <th>名前</th>
                <th>メールアドレス</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['contact_id']; ?></td>
                <td><input type="text" class="form-control" value="<?php echo $row['name']; ?>"></td>
                <td><input type="text" class="form-control" value="<?php echo $row['email']; ?>"></td>
                <td><button class="btn btn-primary update-btn">更新</button></td>
                <td><button class="btn btn-danger delete-btn">削除</button></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <button class="btn btn-success" id="add-btn">+</button>
</div>

<script>
$(document).ready(function() {

    // Update function
    $(document).on('click', '.update-btn', function() {
    var row = $(this).closest("tr");
    var contact_id = row.find("td:nth-child(1)").text();
    var name = row.find("td:nth-child(2)").find("input").val();
    var email = row.find("td:nth-child(3)").find("input").val();

        $.post("contact_functions.php", { action: "update", contact_id: contact_id, name: name, email: email })
            .done(function() {
                alert("Updated successfully!");
            })
            .fail(function() {
                alert("Error updating contact.");
            });
    });

    // Delete function
    $(document).on('click', '.delete-btn', function() {
        var row = $(this).closest("tr");
        var contact_id = row.find("td:nth-child(1)").text();

        $.post("contact_functions.php", { action: "delete", contact_id: contact_id })
            .done(function() {
                row.remove();
                alert("Deleted successfully!");
            })
            .fail(function() {
                alert("Error deleting contact.");
            });
    });

    // Add function
    $("#add-btn").click(function() {
        var newRow = $("<tr><td></td><td><input type='text' class='form-control'></td><td><input type='text' class='form-control'></td><td><button class='btn btn-primary confirm-btn'>Confirm</button></td><td><button class='btn btn-danger delete-btn'>Delete</button></td></tr>");
        $("#contacts-table").append(newRow);
    });

    // Confirm add function
    $(document).on('click', '.confirm-btn', function() {
    var row = $(this).closest("tr");
    var name = row.find("td:nth-child(2)").find("input").val();
    var email = row.find("td:nth-child(3)").find("input").val();


        $.post("contact_functions.php", { action: "add", name: name, email: email }, function(response) {
            row.find("td:nth-child(1)").text(response.contact_id);
            row.find("td:nth-child(4)").html("<button class='btn btn-primary update-btn'>Update</button>");
        }, "json")
        .done(function() {
            alert("Added successfully!");
        })
        .fail(function() {
            alert("Error adding contact.");
        });
    });
});
</script>

<?php include_once 'admin_footer.php'; ?>
