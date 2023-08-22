<?php include_once 'admin_header.php'; ?>
<style>
    .highlight {
    background-color: yellow;
}
</style>
<script>
$(function() {
    $("#sortable").sortable({
        update: function(event, ui) {
            var order = {};
            $('tr', this).each(function(index, element) {
                var article_id = $(element).data('article-id');
                order[article_id] = index;
            });

            $.ajax({
                url: 'update_order.php',
                type: 'POST',
                data: JSON.stringify(order),
                contentType: 'application/json; charset=utf-8',
                success: function(response) {
                    console.log(response);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    }).disableSelection();
    $("#searchArticle").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#articleTable tr").each(function() {
        var articleName = $(this).find('td:first').text().toLowerCase(); // Target the first <td> which is "品名"
        var matched = articleName.indexOf(value) > -1;
        $(this).toggle(matched);

        if (matched) {
            var regex = new RegExp(value, 'gi');
            // Highlight the matched text in the "品名" column
            var highlightedText = articleName.replace(regex, function(match) {
                return "<span class='highlight'>" + match + "</span>";
            });
            $(this).find('td:first').html(highlightedText);
        }
    });
});

$('#belowThresholdFilter, #outOfStockFilter').on('change', function() {
    $("#articleTable tr").each(function() {
        let articleStock = parseInt($(this).find('input[name="data[' + $(this).data('article-id') + '][stock]"]').val(), 10);
        let articleThreshold = parseInt($(this).find('input[name="data[' + $(this).data('article-id') + '][threshold]"]').val(), 10);

        if ($('#belowThresholdFilter').is(':checked') && articleStock > articleThreshold) {
            $(this).hide();
        } else if ($('#outOfStockFilter').is(':checked') && articleStock !== 0) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
});
});
$(document).on('click', '.btn-primary', function() {
    var articleId = $(this).data('id');
    // Fetch the article data based on the ID (e.g., using AJAX)
    // Populate the modal fields with the fetched data
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


