<?php
$dbHost = '127.0.0.1';
$dbUsername = 'testUser';
$dbPassword = '12345';
$dbName = 'IT490';

function validate_session($sessionId)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }

    $sessionId = $conn->real_escape_string($sessionId);
    $query = "SELECT data FROM sessions WHERE SessionID = '$sessionId' AND ExpiryTime > NOW()";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        echo "Valid sessionID confirmed." . PHP_EOL;
        return array('returnCode' => 1, 'message' => 'Valid Session ID.');
    } else {
        echo "Invalid sessionID confirmed." . PHP_EOL;
        return array('returnCode' => 0, 'message' => 'Invalid Session ID.');
    }
}

function read_session($sessionId)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }

    $sessionId = $conn->real_escape_string($sessionId);
    $query = "SELECT data FROM sessions WHERE SessionID = '$sessionId' AND ExpiryTime > NOW()";
    $result = $conn->query($query);

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return array('returnCode' => 1, 'message' => 'Session data found', 'data' => $row['data']);
    } else {
        return array('returnCode' => 0, 'message' => 'Session data could not be read');
    }
}

function write_session($sessionId, $sessionData)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }

    $sessionId = $conn->real_escape_string($sessionId);
    $sessionData = $conn->real_escape_string($sessionData);
    $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $query = "INSERT INTO sessions (SessionID, data, CreationTime, ExpiryTime) 
              VALUES ('$sessionId', '$sessionData', NOW(), '$expiryTime') 
              ON DUPLICATE KEY UPDATE data = VALUES(data), ExpiryTime = VALUES(ExpiryTime)";
    $conn->query($query);

    return array('returnCode' => 1, 'message' => 'Session data written.');
}

function destroy_session($sessionId)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }

    $sessionId = $conn->real_escape_string($sessionId);
    $query = "DELETE FROM sessions WHERE SessionID = '$sessionId'";
    $conn->query($query);

    return array('returnCode' => 1, 'message' => 'Session Destroyed.');
}

function garbage_collection()
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }

    $query = "DELETE FROM sessions WHERE ExpiryTime <= NOW()";
    $conn->query($query);

    return array('returnCode' => 1, 'message' => 'Garbage collection completed.');
}
