<?php
require_once('sessions_handler.php');
require_once('log_maker.php');

class loginDB
{
private $logindb;

public function __construct()
{
	$this->logindb = new mysqli("127.0.0.1","testUser","12345","IT490");

	if ($this->logindb->connect_errno != 0)
	{
		log_this("Error connecting to database: ".$this->logindb->connect_error.PHP_EOL);
		exit(1);
	}
	log_this("correctly connected to database".PHP_EOL);
}

public function validateLogin($username,$password)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	$statement = "select * from users where Username = '$un'";
	$response = $this->logindb->query($statement);

	while ($row = $response->fetch_assoc())
	{
		echo "checking password for $username".PHP_EOL;
		if ($row["Password"] == $pw)
		{	
			garbage_collection();
			$sessionID = session_create_id();
			$write_session = write_session($sessionID, $un);
			if ($sessionID != NULL){
				echo "passwords match for $username".PHP_EOL;
				return array('returnCode' => 1, 'message' => 'Login Successful.', 'sessionID' => $sessionID);
			}else{
				return array('returnCode' => 0, 'message'=> 'Error creating session.');
			}
		}
		echo "passwords did not match for $username".PHP_EOL;
		return array('returnCode' => 0, 'message' => 'Login Failed.');
	}
}
public function createUser($username, $password, $email)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	$em = $this->logindb->real_escape_string($email);
	$statement = "select * from users where Username = '$un'";
	$results = $this->logindb->query($statement);
	
	if($results->num_rows > 0){
		echo "account with username already exists".PHP_EOL;
			return array('returnCode' => 0, 'message' => 'Username already exists.');
	}else{
		$addUser = "INSERT INTO users (Username, Password, Email) VALUES ('$un', '$pw', '$em')";
		if($this->logindb->query($addUser)){
			echo "new user has been created".PHP_EOL;
				return array('returnCode' => 1, 'message' => 'New User Created.');
			}else{
				return array('returnCode' => 0, 'message' => 'Unable to Create New User.');
			}
		}
	}
}

?>
