<?php
require_once('sessions_handler.php');

class ratereturndb
{
private $logindb;

public function __construct()
{
	$this->ratereturndb = new mysqli("1270.1","testUser","12345","IT490");

	if ($this->ratereturndb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->ratedb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

	
public function ratereturn($accountID)
{
	$aid = $this->ratereturndb->real_escape_string($accountID);
	$statement = "SELECT * FROM ratings WHERE accountID = '$aid'";
	$results = $this->ratereturndb->query($statement);
	
	while ($row = mysqli_fetch_array($results)) {
		//return meal id and rating
	}
	
	if($results->num_rows > 0){
		echo "account".PHP_EOL;
			return array('returnCode' => 0, 'message' => 'Username already exists.');
	}else{
		$updateRating = "INSERT INTO ratings (mealID, accountID, rating) VALUES ('$mid', '$aid', '$rate')";
		if($this->ratedb->query($addUser)){
			echo "new rating was made on this recipe".PHP_EOL;
				return array('returnCode' => 1, 'message' => 'Recipe Rating Saved');
			}else{
				return array('returnCode' => 0, 'message' => 'Unable to Update Recipe Rating.');
			}
		}
	}
}
