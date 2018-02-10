<?php

require 'db/db.php';

$db = new Db();

$response = $db->mark_complete_by_id($_GET['id']);

//go back to the main page
//header("location: index.php");
?>
<html>
    <body>
    <p>You will be redirected in 3 seconds</p>
    <script>
        var timer = setTimeout(function() {
            window.location='index.php'
        }, 3000);
    </script>
</body>
</html>


