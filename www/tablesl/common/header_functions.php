
    <script>
        var HTML_PATH = "<?php echo PUBLIC_PATH;?>";

        var options = {
            ajaxAnswerValid: 'ok'
        };

        function redirect(tableName){
            <?php if(REWRITE_ENABLED) : ?>
                window.location = "../list/"+tableName+".html";
                <?php else : ?>
                window.location = "../list.php?table="+tableName;
                <?php endif; ?>
        }


    </script>
