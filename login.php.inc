<?php
require_once('sessions_handler.php');

class loginDB
{
private $logindb;

public function __construct()
{
	$this->logindb = new mysqli("127.0.0.1","testUser","12345","IT490");

	if ($this->logindb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

function validateLogin($username, $password)
{
    $un = $this->logindb->real_escape_string($username);
    $encrypted_password = hash('sha256', $password);
    $pw = $this->logindb->real_escape_string($encrypted_password);
    $statement = "SELECT * FROM users WHERE Username = '$un'";
    $response = $this->logindb->query($statement);

    while ($row = $response->fetch_assoc())
    {
        echo "Checking password for $username" . PHP_EOL;
        if ($row["Password"] == $pw)
        {   
            garbage_collection();
            $sessionID = session_create_id();
            $write_session = write_session($sessionID, $un);
            if ($sessionID != NULL){
                echo "Passwords match for $username" . PHP_EOL;
                
                // Check if 2FA is enabled or disabled
                if ($row["2FA"] === 'enabled') {
                    return array('returnCode' => 2, 'message' => '2FA Verification Required.', 'sessionID' => $sessionID, 'userID' => $row["UserId"]);
                } else {
                    return array('returnCode' => 1, 'message' => 'Login Successful.', 'sessionID' => $sessionID, 'userID' => $row["UserId"]);
                }
            } else {
                return array('returnCode' => 0, 'message' => 'Error creating session.');
            }
        }
        echo "Passwords did not match for $username" . PHP_EOL;
        return array('returnCode' => 0, 'message' => 'Login Failed.');
    }
}

function createUser($username, $password, $email, $twoFactor)
{
	$un = $this->logindb->real_escape_string($username);
	$encrypted_password = hash('sha256', $password);
	$pw = $this->logindb->real_escape_string($encrypted_password);
	$em = $this->logindb->real_escape_string($email);
	$tf = $this->logindb->real_escape_string($twoFactor);
	$statement = "select * from users where Username = '$un'";
	$results = $this->logindb->query($statement);
	
	if($results->num_rows > 0){
		echo "account with username already exists".PHP_EOL;
			return array('returnCode' => 0, 'message' => 'Username already exists.');
	}else{
		$addUser = "INSERT INTO users (Username, Password, Email, 2FA) VALUES ('$un', '$pw', '$em', '$tf')";
		if($this->logindb->query($addUser)){
			echo "new user has been created".PHP_EOL;
				return array('returnCode' => 1, 'message' => 'New User Created.');
			}else{
				return array('returnCode' => 0, 'message' => 'Unable to Create New User.');
			}
		}
	}
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function validateAuthentication($userID, $userInputAuthCode) {
    $currentDateTime = date("Y-m-d H:i:s");
    $fortyEightHoursAgo = date("Y-m-d H:i:s", strtotime('-48 hours'));

    $checkStatement = "SELECT * FROM twoFactorAuth WHERE UserId = '$userID' AND LastAuthDateTime >= '$fortyEightHoursAgo'";
    $result = $this->logindb->query($checkStatement);

    if ($result && $result->num_rows > 0) {
        return array('returnCode' => 1, 'message' => '2FA is enabled and LastAuthDateTime was within the past 48 hours.', 'status' => true);
    } else {
        $passStringCheckStatement = "SELECT * FROM twoFactorAuth WHERE UserId = '$userID' AND LastAuthDateTime < '$fortyEightHoursAgo'";
        $passStringResult = $this->logindb->query($passStringCheckStatement);

        if ($passStringResult && $passStringResult->num_rows > 0) {
            $passStringRow = $passStringResult->fetch_assoc();
            $passString = $passStringRow['PassString'];

            $emailStatement = "SELECT Email FROM users WHERE UserId = '$userID'";
            $emailResult = $this->logindb->query($emailStatement);

            if ($emailResult && $emailResult->num_rows > 0) {
                $row = $emailResult->fetch_assoc();
                $userEmail = $row['Email'];

                $emailMessage = "Here is your authentication code: $passString";

                $emailSent = sendEmail($userEmail, "Authentication Code for Login", $emailMessage);

                if ($emailSent) {
                    return array('returnCode' => 0, 'message' => '2FA authentication code sent to user.', 'status' => false);
                } else {
                    return array('returnCode' => 0, 'message' => 'Failed to send email.', 'status' => false);
                }
            } else {
                return array('returnCode' => 0, 'message' => 'Failed to fetch user email.', 'status' => false);
            }
        } else {
            return array('returnCode' => 0, 'message' => '2FA not enabled or LastAuthDateTime within the past 48 hours not found.', 'status' => false);
        }
    }
}

function sendEmail($to, $subject, $message) {
    $from = 'help@goodEats.com';
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}

function validateAuthLife($userId){
    function validateAuthLife($userId)
{
    $userId = $this->logindb->real_escape_string($userId);
    $sql = "SELECT LastAuthDateTime FROM twoFactorAuth WHERE UserId = '$userId' LIMIT 1";
    $response = $this->logindb->query($sql);

    while ($row = $response->fetch_assoc()) {
        $lastAuthDateTime = strtotime($row['LastAuthDateTime']);
        $currentDateTime = time();
        $timeDiff = $currentDateTime - $lastAuthDateTime;

        if ($timeDiff <= 48 * 3600) {
            return array('returnCode' => 1, 'message' => 'Authentication life validated.');
        } else {
            return array('returnCode' => 0, 'message' => 'Authentication life expired.');
        }
    }

    return array('returnCode' => 0, 'message' => 'User not found or error occurred.');
}
}

?>
