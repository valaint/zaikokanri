<?php include_once 'admin_header.php'; ?>
<style>
    .highlight {
    background-color: yellow;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    // Sortable via SortableJS instead of jQuery UI
    var sortableList = document.getElementById('sortable');
    if (sortableList) {
        new Sortable(sortableList, {
            animation: 150,
            onUpdate: function (evt) {
                var order = {};
                var rows = sortableList.querySelectorAll('tr');
                rows.forEach(function(row, index) {
                    var article_id = row.getAttribute('data-article-id');
                    if (article_id) {
                        order[article_id] = index;
                    }
                });

                fetch('update_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json; charset=utf-8' },
                    body: JSON.stringify(order)
                })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error => console.log(error));
            }
        });
    }

    const searchInput = document.getElementById("searchArticle");
    if (searchInput) {
        searchInput.addEventListener("keyup", function() {
            var value = this.value.toLowerCase();
            var rows = document.querySelectorAll("#sortable tr");

            rows.forEach(function(row) {
                var firstCell = row.cells[0];
                var articleName = firstCell.textContent.toLowerCase();
                var matched = articleName.indexOf(value) > -1;

                row.style.display = matched ? '' : 'none';

                if (matched && value !== '') {
                    var regex = new RegExp(value, 'gi');
                    firstCell.innerHTML = articleName.replace(regex, function(match) {
                        return "<span class='highlight'>" + match + "</span>";
                    });
                } else if (matched && value === '') {
                    firstCell.innerHTML = articleName;
                }
            });
        });
    }

    const belowThresholdFilter = document.getElementById('belowThresholdFilter');
    const outOfStockFilter = document.getElementById('outOfStockFilter');

    function applyFilters() {
        var rows = document.querySelectorAll("#sortable tr");
        var belowChecked = belowThresholdFilter.checked;
        var outOfChecked = outOfStockFilter.checked;

        rows.forEach(function(row) {
            var articleId = row.getAttribute('data-article-id');
            var stockInput = row.querySelector('input[name="data[' + articleId + '][stock]"]');
            var thresholdInput = row.querySelector('input[name="data[' + articleId + '][threshold]"]');

            if (!stockInput || !thresholdInput) return;

            var articleStock = parseInt(stockInput.value, 10);
            var articleThreshold = parseInt(thresholdInput.value, 10);

            if (belowChecked && articleStock > articleThreshold) {
                row.style.display = 'none';
            } else if (outOfChecked && articleStock !== 0) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
            }
        });
    }

    if (belowThresholdFilter) belowThresholdFilter.addEventListener('change', applyFilters);
    if (outOfStockFilter) outOfStockFilter.addEventListener('change', applyFilters);

    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-primary')) {
            var articleId = e.target.getAttribute('data-id');
            if (articleId) {
                // Fetch the article data based on the ID
                // Populate the modal fields with the fetched data
            }
        }
    });
});
</script>

<div class="container">
<input type="text" id="searchArticle" placeholder="Search for articles...">
<label><input type="checkbox" id="belowThresholdFilter"> Below Threshold</label>
<label><input type="checkbox" id="outOfStockFilter"> Out of Stock</label>
    <form action='update_article.php' method='post'>
    <table class="table table-striped table-hover" id="articleTable">
            <thead class="thead-dark">
                <tr>
                    <th>品名</th>
                    <th>担当者</th>
                    <th>在庫数</th>
                    <th>閾値</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="sortable">
                <?php
                $stmt = $con->prepare("SELECT article_info.article_id, article_info.article_name, contact.name, contact.contact_id, article_info.stock, article_info.threshold 
                                       FROM article_info 
                                       JOIN contact ON article_info.contact_id1 = contact.contact_id
                                       ORDER BY article_info.article_order");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<tr data-article-id='{$row['article_id']}'>
                        <td>{$row['article_name']}</td>
                        <td>
                            <select class='form-control' name='data[{$row['article_id']}][contact_id]'>";

                    // Fetch all contacts and create an option for each one
                    $contact_stmt = $con->prepare("SELECT contact_id, name FROM contact");
                    $contact_stmt->execute();
                    $contact_result = $contact_stmt->get_result();
                    while ($contact_row = $contact_result->fetch_assoc()) {
                        $selected = ($contact_row['contact_id'] == $row['contact_id']) ? "selected='selected'" : "";
                        echo "<option value='{$contact_row['contact_id']}' {$selected}>{$contact_row['name']}</option>";
                    }
                    $contact_result->free();

                    echo "</select>
                        </td>
                        <td><input class='form-control' type='number' name='data[{$row['article_id']}][stock]' value='{$row['stock']}'></td>
                        <td><input class='form-control' type='number' name='data[{$row['article_id']}][threshold]' value='{$row['threshold']}'></td>
                        <td>
                        <a class='btn btn-primary' href='#' data-toggle='modal' data-target='#editModal' data-id='{$row['article_id']}'>詳細</a>
                        <button class='btn btn-danger delete-btn' data-id='{$row['article_id']}' data-name='{$row['article_name']}'>削除</button>
                        </td>
                    </tr>";
                }
                $result->free();
                ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-success">すべて更新</button>
    </form>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Article</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            <input type="text" name="article_name" placeholder="Article Name">
            <input type="text" name="stock" placeholder="Stock">
            <input type="text" name="threshold" placeholder="Threshold">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'admin_footer.php'; ?>


