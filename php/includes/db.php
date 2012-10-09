<?php
// Connecting & selecting database
$dbconn = pg_connect("host=localhost dbname=photo_accounting user=photo_editor password=Htbp4SAaxm6K")
    or die('Could not connect: ' . pg_last_error());
?>
