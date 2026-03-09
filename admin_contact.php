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
            <?php while ($row = $result->fetch_assoc()) : ?>
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
document.addEventListener("DOMContentLoaded", function() {

    // Helper for POST requests
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

    // Event delegation for table buttons
    document.getElementById("contacts-table").addEventListener("click", function(e) {
        const target = e.target;

        if (target.classList.contains("update-btn")) {
            const row = target.closest("tr");
            const contact_id = row.cells[0].innerText.trim();
            const name = row.cells[1].querySelector("input").value;
            const email = row.cells[2].querySelector("input").value;

            postData("contact_functions.php", { action: "update", contact_id: contact_id, name: name, email: email })
                .then(response => {
                    if (response.ok) {
                        alert("Updated successfully!");
                    } else {
                        alert("Error updating contact.");
                    }
                })
                .catch(err => alert("Error updating contact."));
        }

        if (target.classList.contains("delete-btn")) {
            const row = target.closest("tr");
            const contact_id = row.cells[0].innerText.trim();

            postData("contact_functions.php", { action: "delete", contact_id: contact_id })
                .then(response => {
                    if (response.ok) {
                        row.remove();
                        alert("Deleted successfully!");
                    } else {
                        alert("Error deleting contact.");
                    }
                })
                .catch(err => alert("Error deleting contact."));
        }

        if (target.classList.contains("confirm-btn")) {
            const row = target.closest("tr");
            const name = row.cells[1].querySelector("input").value;
            const email = row.cells[2].querySelector("input").value;

            postData("contact_functions.php", { action: "add", name: name, email: email })
                .then(response => response.json())
                .then(data => {
                    if (data && data.contact_id) {
                        row.cells[0].innerText = data.contact_id;
                        row.cells[3].innerHTML = "<button class='btn btn-primary update-btn'>Update</button>";
                        alert("Added successfully!");
                    } else {
                        alert("Error adding contact.");
                    }
                })
                .catch(err => alert("Error adding contact."));
        }
    });

    // Add function
    document.getElementById("add-btn").addEventListener("click", function() {
        const tbody = document.querySelector("#contacts-table tbody");
        const newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td></td>
            <td><input type='text' class='form-control'></td>
            <td><input type='text' class='form-control'></td>
            <td><button class='btn btn-primary confirm-btn'>Confirm</button></td>
            <td><button class='btn btn-danger delete-btn'>Delete</button></td>
        `;
        tbody.appendChild(newRow);
    });
});
</script>

<?php include_once 'admin_footer.php'; ?>
