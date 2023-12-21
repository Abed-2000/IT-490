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

    public function validateLogin($username, $password)
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

    public function createUser($username, $password, $email, $twoFactor)
    {
        $un = $this->logindb->real_escape_string($username);
        $encrypted_password = hash('sha256', $password);
        $pw = $this->logindb->real_escape_string($encrypted_password);
        $em = $this->logindb->real_escape_string($email);
        $tf = $this->logindb->real_escape_string($twoFactor);
        $statement = "SELECT * FROM users WHERE Username = '$un'";
        $results = $this->logindb->query($statement);
        
        if ($results->num_rows > 0) {
            echo "Account with username already exists" . PHP_EOL;
            return array('returnCode' => 0, 'message' => 'Username already exists.');
        } else {
            $addUser = "INSERT INTO users (Username, Password, Email, 2FA) VALUES ('$un', '$pw', '$em', '$tf')";
            if ($this->logindb->query($addUser)) {
                echo "New user has been created" . PHP_EOL;
                if ($twoFactor === 'enabled') {
                    $userId = $this->logindb->insert_id;
                    $passString = substr(md5(mt_rand()), 0, 10);
                    $oldDateTime = '2000-01-01 00:00:00';
                    $insertTwoFactor = "INSERT INTO twoFactorAuth (UserId, Email, LastAuthDateTime, PassString) 
                                        VALUES ('$userId', '$em', '$oldDateTime', '$passString')";
                    if ($this->logindb->query($insertTwoFactor)) {
                        return array('returnCode' => 1, 'message' => 'New User Created.');
                    } else {
                        return array('returnCode' => 0, 'message' => 'Unable to Create New User.');
                    }
                } else {
                    return array('returnCode' => 1, 'message' => 'New User Created without 2FA.');
                }
            } else {
                return array('returnCode' => 0, 'message' => 'Unable to Create New User.');
            }
        }
    }
}

?>
