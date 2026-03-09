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
    while ($row = $result1->fetch_assoc()) {
        $barcode_counts[$row['barcode']][] = $row;
    }
    foreach ($barcode_counts as $barcode => $rows) {
        $count = count($rows);
        foreach ($rows as $index => $row) {
            echo '<tr>';
            if ($index === 0) {
                echo '<td rowspan="' . $count . '"><input type="text" class="form-control" value="' . $barcode . '"></td>';
            }
            echo '
        <td>
          <select class="form-control">';
            foreach ($articles as $id => $name) {
                $selected = $id == $row["article_id"] ? "selected" : "";
                echo '<option value="' . $id . '" ' . $selected . '>' . $name . '</option>';
            }
            echo '
          </select>
        </td>
        <td><input type="text" class="form-control" value="' . $row["destock_count"] . '"></td>
        <td><input type="checkbox" class="form-check-input" ' . ($row["is_prompt"] ? "checked" : "") . '><input type="hidden" class="id-input" value="' . $row["id"] . '"></td>
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
      <?php while ($row = $result3->fetch_assoc()) : ?>
      <tr>
        <td><input type="text" class="form-control" value="<?= $row["barcode"] ?>"></td>
        <td>
          <select class="form-control">
            <?php foreach ($articles as $id => $name) : ?>
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
            <?php foreach ($articles as $id => $name) : ?>
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

    document.querySelectorAll(".table-bordered").forEach(table => {
        table.addEventListener("click", function(e) {
            const target = e.target;

            // Update function
            if (target.classList.contains("btn-primary") && target.textContent.trim() !== "追加") {
                const row = target.closest("tr");
                const idInput = row.querySelector(".id-input");
                const id = idInput ? idInput.value : "";
                // Logic to handle rowspan for barcode where input might be in previous row
                let barcodeInput = row.querySelector("td:nth-child(1) input[type='text']");
                let barcode = barcodeInput ? barcodeInput.value : "";

                let articleSelectIndex = barcodeInput ? 1 : 0;
                let destockIndex = barcodeInput ? 2 : 1;
                let promptIndex = barcodeInput ? 3 : 2;

                const article_id = row.cells[articleSelectIndex].querySelector("select").value;
                const destock_count = row.cells[destockIndex].querySelector("input").value;
                const is_prompt = row.cells[promptIndex].querySelector("input[type='checkbox']").checked ? 1 : 0;

                postData("barcode_functions.php", { action: "update", id: id, barcode: barcode, article_id: article_id, destock_count: destock_count, is_prompt: is_prompt })
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
                const idInput = row.querySelector(".id-input");
                const id = idInput ? idInput.value : "";
                let barcodeInput = row.querySelector("td:nth-child(1) input[type='text']");
                let barcode = barcodeInput ? barcodeInput.value : "";

                postData("barcode_functions.php", { action: "delete", id: id, barcode: barcode })
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
                const barcode = row.cells[0].querySelector("input").value;
                const article_id = row.cells[1].querySelector("select").value;
                const destock_count = row.cells[2].querySelector("input").value;
                const is_prompt = row.cells[3].querySelector("input[type='checkbox']").checked ? 1 : 0;

                postData("barcode_functions.php", { action: "add", barcode: barcode, article_id: article_id, destock_count: destock_count, is_prompt: is_prompt })
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.barcode) {
                            const newRow = document.createElement("tr");
                            newRow.innerHTML = `
                                <td><input type="text" class="form-control" value="${data.barcode}"></td>
                                <td>
                                    <select class="form-control">
                                        <option value="${article_id}" selected>${article_id}</option>
                                    </select>
                                </td>
                                <td><input type="text" class="form-control" value="${destock_count}"></td>
                                <td><input type="checkbox" class="form-check-input" ${is_prompt ? 'checked' : ''}></td>
                                <td><button class="btn btn-primary">Update</button> <button class="btn btn-danger">Delete</button></td>
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
});
</script>