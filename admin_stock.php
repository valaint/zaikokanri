<?php include_once 'admin_header.php'; ?>

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
});
</script>

<div class="container">
    <form action='update_article.php' method='post'>
        <table class="table table-striped table-hover">
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
<?php include_once 'admin_footer.php'; ?>


