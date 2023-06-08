<?php
    require_once('../../server/api-adodb/config.php');
    $table = new Table();

?>
<!DOCTYPE>
<html>
    <head>
        <title>CMS GENERATOR V.3.1</title>
        <link href="css/styles2.css" media="all" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="tools/jquery-1.10.2.min.js"></script>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script>
        $(document).ready(function(){
            $("#create_tables").click(function(){
                var db = $("#db").val();
                if (db == '') {
                    alert ("Please select a db first!");
                    return false;
                }
                window.location.href = "createTables.php?db="+db;
            });
        });
        </script>
    </head>
    <body>
    <div style="width:300px; background:#fff; padding:10px; margin:20px auto; text-align:center; border:thin solid #dcc6c6;" class="round drop_shadow">
    <img src="<?php echo ADMIN_PATH_HTML.DS; ?>images/logo-outside.png" /><br /><br />
        <h1>CMS GENERATOR V.3.1</h1><br />

        <form action="showDatabase.php" method="post">

            <select name="database" id='db'>
                <option  value="" disabled="disabled" style="font-weight:bold;" selected>Choose a Database</option>
                <?php $result = mysqli_query('SHOW DATABASES');?>
                <?php while ($row = mysqli_fetch_array($result)) {?>
                    <?php print_r($row);?>
                    <?php if($row['Database']=='information_schema' || $row['Database']=='mysql') {continue;}?>
                    <option <?php if (DB_NAME == $row['Database']) { echo "selected='selected'"; } else { echo 'disabled'; } ?> value="<?php echo $row['Database']?>"><?php echo $row['Database']?></option>
                    <?php }?>
            </select>
            <br />
            <br />
            <input type="submit" class="submit round drop_shadow" value="Configure CMS" /> <input type="button" id='create_tables' class="submit round drop_shadow" value="Create Tables" />
        </form>
        </div>
    </body>
    </html>
