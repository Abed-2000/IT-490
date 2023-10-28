<?php
require_once('sessions_handler.php');

class ratedb
{
private $logindb;

public function __construct()
{
	$this->ratedb = new mysqli("1270.1","testUser","12345","IT490");

	if ($this->ratedb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->ratedb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

	
public function rate($mealID, $accountID, $rating)
{
	$mid = $this->logindb->real_escape_string($mealID);
	$aid = $this->logindb->real_escape_string($accountID);
	$rate = $this->logindb->real_escape_string($rating);
	$statement = "select * from ratings where mealID = '$mid' AND accountID = '$aid'";
	$results = $this->logindb->query($statement);
	
	if($results->num_rows > 0){
		echo "UPDATE TABLE ratings SET rating = '$rate' WHERE mealID = '$mid' AND accountID = '$aid'".PHP_EOL;
			return array('returnCode' => 1, 'message' => 'Rating successfully updated.');
	}else{
		$updateRating = "INSERT INTO ratings (mealID, accountID, rating) VALUES ('$mid', '$aid', '$rate')";
		if($this->logindb->query($addUser)){
			echo "new rating was made on this recipe".PHP_EOL;
				return array('returnCode' => 1, 'message' => 'Recipe Rating Saved');
			}else{
				return array('returnCode' => 0, 'message' => 'Unable to Update Recipe Rating.');
			}
		}
	}
}
?>
