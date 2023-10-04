<?php

require_once('databaseConnection.inc');

function validateLogin($database, $user, $pass){
    $username = $database->real_escape_string($user);
    $password = $database->real_escape_string($pass);
    $query = "SELECT * FROM users WHERE `Username` = '$username'";

    $result = database -> query($query);

    if (!$result) {
        die("Query failed: " . $database->error);
    }
    
	while ($row = $response->fetch_assoc())
	{
		echo "checking password for $username".PHP_EOL;
		if ($row["Password"] == $password)
		{
			echo "passwords match for $username".PHP_EOL;
			return 1;// password match
		}
		echo "passwords did not match for $username".PHP_EOL;
	}
	return 0;//no users matched username

    $database->close();

}
?>