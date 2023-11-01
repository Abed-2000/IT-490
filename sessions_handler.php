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

function rateRecipe($mealID, $accountID, $rating)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }
    $mid = $conn->real_escape_string($mealID);
    $aid = $conn->real_escape_string($accountID);
    $rate = $conn->real_escape_string($rating);
    $statement = "select * from ratings where mealID = '$mid' AND accountID = '$aid'";
    $results = $conn->query($statement);

    if($results->num_rows > 0){
        $updateRating = "UPDATE ratings SET rating = '$rate' WHERE mealID = '$mid' AND accountID = '$aid'";
        if($conn->query($updateRating)){
            echo "rating was updated".PHP_EOL;
            return array('returnCode' => 1, 'message' => 'Rating successfully updated.');
          }
          else{
          return array('returnCode' => 0, 'message' => "Unable to update ratings");
         }
    }else{
        $createRating = "INSERT INTO ratings (mealID, accountID, rating) VALUES ('$mid', '$aid', '$rate')";
        if($conn->query($createRating)){
            echo "new rating was made on this recipe".PHP_EOL;
                return array('returnCode' => 1, 'message' => 'Recipe Rating Saved');
            }else{
                return array('returnCode' => 0, 'message' => 'Unable to Create Recipe Rating.');
            }
        }
    } 

function saveRecipe($formDataArrayJSON) {
    $formDataArray = json_decode($formDataArrayJSON, true);

    if ($formDataArray === null) {
        return array("returnCode" => 0, "message" => "Invalid JSON data");
    }

    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to the database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }

    foreach ($formDataArray as $formData) {
        $columnNames = array();
        $placeholders = array();
        $values = array();

        foreach ($formData as $key => $value) {
            if ($key !== 'datePublished' && $key !== 'created_by') {
                $columnNames[] = $key;
                $placeholders[] = '?';
                $values[] = $value;
            }
        }

        $ingredients = $formData['strIngredient'];
        $measures = $formData['strMeasure'];

        for ($i = 0; $i < count($ingredients); $i++) {
            $ingredientKey = 'strIngredient' . ($i + 1);
            $measureKey = 'strMeasure' . ($i + 1);

            $columnNames[] = $ingredientKey;
            $columnNames[] = $measureKey;
            $placeholders[] = '?';
            $placeholders[] = '?';
            $values[] = $ingredients[$i];
            $values[] = $measures[$i];
        }

        $sql = "INSERT INTO custom_meals (" . implode(", ", $columnNames) . ") " .
               "VALUES (" . implode(", ", $placeholders) . ")";

        $stmt = $conn->prepare($sql);
echo $sql;
        $bindTypes = str_repeat('s', count($values));
        $stmt->bind_param($bindTypes, ...$values);

        $stmt->execute();
        $stmt->close();
         return array("returnCode" => 1, "message" => "Meal was successfully saved.");
    }

    $conn->close();

}
function searchMeals($query) {
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        return array("returnCode" => 0, "message" => "Error connecting to the database: " . $conn->connect_error);
    }

    $query = '%' . $conn->real_escape_string($query) . '%';
    $sql = "SELECT id, strMeal, strArea, strMealThumb FROM custom_meals WHERE strCategory LIKE ? OR strArea LIKE ? OR strMeal LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $query, $query, $query);
    $stmt->execute();

    $result = $stmt->get_result();
    $meals = array();

    while ($row = $result->fetch_assoc()) {
        $meals[] = $row;
    }

    $stmt->close();
    $conn->close();

    if (count($meals) > 0) {
        return array("returnCode" => 1, "message" => $meals);
    } else {
        return array("returnCode" => 0, "message" => "No matching meals found.");
    }
}
    function mealDetails($query){
        global $dbHost, $dbUsername, $dbPassword, $dbName;
        $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
        
        if ($conn->connect_error) {
            return array("returnCode" => 0, "message" => "Error connecting to the database: " . $conn->connect_error);
        }
        $query = $conn->real_escape_string($query); 
        $sql = "SELECT * FROM custom_meals WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $query);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $mealData = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        if ($mealData) {
            return array("returnCode" => 1, "message" => $mealData);
        } else {
            return array("returnCode" => 0, "message" => "Meal not found in the database.");
        }
    }
function doShare($mealID, $accountID)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }
    $mid = $conn->real_escape_string($mealID);
    $aid = $conn->real_escape_string($accountID);
    $statement = "SELECT * FROM user_saves WHERE mealID = '$mid' AND userID = '$aid'";
    $results = $conn->query($statement);

    if($results->num_rows > 0){
      $createSave = "INSERT INTO user_saves (mealID,userID) VALUES ('$mid', '$aid')";
      if($conn->query($createSave)){
      	 echo "new save made".PHP_EOL;
                return array('returnCode' => 1, 'message' => 'Recipe Save Saved');
            }else{
                return array('returnCode' => 0, 'message' => 'Unable to Create Recipe Save.');
            }
      }
      else{
      	echo "already saved".PHP_EOL;
      		return array('returnCode' =>0, 'message' => 'Already saved.');
      }
}
?>
