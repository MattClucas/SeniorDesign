<?php
	$db = new mysqli("localhost", "michaeldmead", "LmXRrV6wVEwhszCd", "michaeldmead");
    if($db->connection_error)
    {
        die("connection failed: " . $db->connection_error);
    }
?>
