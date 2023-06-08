<?php
require_once('../../server/api-adodb/config.php');

if (!isset($_GET['db'])) {
    die("no db!");
}

$table = new Table();



?>
<!DOCTYPE>
<html>
    <head>
        <title>CMS GENERATOR V.3.1</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" href="tools/styles/github.css">
        <link rel="stylesheet" href="tools/styles/styles.css">
        <script type="text/javascript" src="tools/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="tools/textext.js"></script>
        <script type="text/javascript" src="tools/jquery.jeditable.js"></script>
        <script type="text/javascript" src="tools/highlight.min.js"></script>
        
        <!--   this file is on github, mostly edited and updated there, check version there always     -->
        <script type="text/javascript" src="tools/scripts.js"></script>

    </head>
    <body>
        <div>

            <div style='text-align: center;'>
                <img src="<?php echo ADMIN_PATH_HTML.DS; ?>images/logo-inside.png" /><br /><br />
                <h1 style='margin:0'>SQL GENERATOR v0.2</h1><br />
            </div>
            <?php

            if (isset($_POST['sqlBox'])){

                $sql_query = split_sql_file($_POST['sqlBox'], ';');

                foreach($sql_query as $query){
                    $table->executeSql($query);
                }

                if ($table->adodb->ErrorMsg()){
                    echo $table->adodb->ErrorMsg();
                    echo "Errors...please execute manually...";
                }
                else {
                    echo "<form action='showDatabase.php' method='post'><input type='hidden' name='database' value='".$_GET['db']."'> <input type='submit' value='Next: Generate CMS'></form>";
                }
                echo "<br /><br /><br /><br />";
            }
            ?>

            <div class='wrap'>
                <textarea class='input' rows="8" cols="50" id="txt">
                    cars:id,name,color,desc
                    categories:ID,name</textarea>
            </div>
            <div class='results wrap'>
                <div id="visualData"></div>
                <br style='clear:both' />
            </div>
            <div class='results wrap'>
                <pre><code id='sql'></code></pre>
                <br style='clear:both' />
            </div>
            <div style='text-align:center'>
                <form id='submitForm' action="" method="post">
                    <textarea id='sqlTextarea' name='sqlBox' style='display:none'></textarea>
                    <input type="submit" class="submit round drop_shadow" value="Execute SQL" />
                </form>
            </div>
        </div>
    </body>
    </html>