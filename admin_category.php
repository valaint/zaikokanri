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
    <?php while ($row = $result->fetch_assoc()) : ?>
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
document.addEventListener("DOMContentLoaded", function() {

    function postData(url, data) {
        const formData = new URLSearchParams();
        for (const key in data) {
            formData.append(key, data[key]);
        }
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
    }

    document.querySelector(".table-bordered").addEventListener("click", function(e) {
        const target = e.target;

        // Update function
        if (target.classList.contains("btn-primary") && target.textContent.trim() !== "追加") {
            const row = target.closest("tr");
            const category_id = row.cells[0].innerText.trim();
            const category_name = row.cells[1].querySelector("input").value;

            postData("category_functions.php", { action: "update", category_id: category_id, category_name: category_name })
                .then(response => {
                    if (response.ok) {
                        alert("Updated successfully!");
                    } else {
                        alert("Error updating entry.");
                    }
                })
                .catch(err => alert("Error updating entry."));
        }

        // Delete function
        if (target.classList.contains("btn-danger")) {
            const row = target.closest("tr");
            const category_id = row.cells[0].innerText.trim();

            postData("category_functions.php", { action: "delete", category_id: category_id })
                .then(response => {
                    if (response.ok) {
                        row.remove();
                        alert("Deleted successfully!");
                    } else {
                        alert("Error deleting entry.");
                    }
                })
                .catch(err => alert("Error deleting entry."));
        }

        // Add function
        if (target.classList.contains("btn-success")) {
            const row = target.closest("tr");
            const category_id = row.cells[0].innerText.trim();
            const category_name = row.cells[1].querySelector("input").value;

            postData("category_functions.php", { action: "add", category_id: category_id, category_name: category_name })
                .then(response => response.json())
                .then(data => {
                    if (data && data.category_id) {
                        const newRow = document.createElement("tr");
                        newRow.innerHTML = `
                            <td>${data.category_id}</td>
                            <td><input type="text" class="form-control" value="${category_name}"></td>
                            <td><button class="btn btn-primary">更新</button> <button class="btn btn-danger">削除</button></td>
                        `;
                        row.parentNode.insertBefore(newRow, row);
                        alert("Added successfully!");
                    } else {
                        alert("Error adding entry.");
                    }
                })
                .catch(err => alert("Error adding entry."));
        }
    });
});
</script>
