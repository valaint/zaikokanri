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

<h2>管理画面へ</h2>
こちらは管理画面です。

<?php include_once 'admin_footer.php'; ?>