<?php

require_once('databaseConnection.inc');

function validateLogin(){
    $database = Connection();
    $clean_username = database -> real_escape_string($username);
    $clean_password = database -> real_escape_string($password);
    $query = "SELECT * FROM users WHERE (`Username` = '$clean_username') AND (`Password` = '$clean_password')";
    $result = database -> query($query);

    if (!$result) {
        die("Query failed: " . $database->error);
    }
    
    $database->close();

}
?>