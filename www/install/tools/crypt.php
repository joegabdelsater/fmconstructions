<?php
require_once('../../../server/api-adodb/config.php');

if ($_POST){
    if (isset($_POST['encrypt'])){
        echo TextEncryption::encrypt($_POST['txt']);
    }else {
        echo TextEncryption::decrypt($_POST['txt']);
    }
    
}
?>


<form method="post">
    <textarea name='txt' rows='10' cols='100'></textarea>
    <br />
    <input type='submit' name='encrypt' value='encrypt' />
    <input type='submit' name='decrypt' value='decrypt' />
</form>