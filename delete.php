<?php

require 'db/db.php';

$db = new Db();

$response = $db->mark_complete_by_id($_GET['id']);

//go back to the main page
header("location: index.php");
?>
