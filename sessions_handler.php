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
        
        echo print_r($mealData);
        
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

function getRank() {
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
        exit(1);
    }

    $sql = "SELECT SUM(r.rating) as totalRating, r.mealID, cm.strMealThumb, cm.strMeal, cm.id AS idMeal
            FROM ratings r
            JOIN custom_meals cm ON r.mealID = cm.id
            GROUP BY r.mealID
            ORDER BY totalRating DESC
            LIMIT 5";

    if ($stmt = $conn->prepare($sql)) {
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
            return array("returnCode" => 0, "message" => "No rankings found.");
        }
    } else {
        echo "Error in SQL query: " . $conn->error . PHP_EOL;
        return array("returnCode" => -1, "message" => "SQL error.");
    }
}

function getMealRating($mealID) {
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to the database: " . $conn->connect_error;
        exit(1);
    }

    $sql = "SELECT SUM(rating) as total_rating FROM ratings WHERE mealid = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo "Error preparing the SQL statement: " . $conn->error;
        exit(1);
    }

    $stmt->bind_param("s", $mealID);
    $stmt->execute();

    $result = $stmt->get_result();

    $ratingData = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    if ($ratingData) {
        return array("returnCode" => 1, "message" => $ratingData);
    } else {
        return array("returnCode" => 0, "message" => "No rating data found.");
    }
}

function validateAuthLife($userId)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;

    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to the database: " . $conn->connect_error;
        exit(1);
    }

    $sql = "SELECT LastAuthDateTime FROM twoFactorAuth WHERE UserId = '$userId' LIMIT 1";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $lastAuthDateTime = strtotime($row['LastAuthDateTime']);
                $currentDateTime = time();
                $timeDiff = $currentDateTime - $lastAuthDateTime;
                if ($timeDiff <= 48 * 3600) {
                    return array('returnCode' => 1, 'message' => 'Authentication life validated.');
                } else {

                    return array('returnCode' => 2, 'message' => 'Authentication life expired.');
                }
            }
        } else {
            return array('returnCode' => 2, 'message' => 'User not found.');
        }
    } else {
        return array('returnCode' => 0, 'message' => 'Error occurred in the query.');
    }
}

function emailAuthCode($userId){
    global $dbHost, $dbUsername, $dbPassword, $dbName;

    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        echo "Error connecting to the database: " . $conn->connect_error;
        exit(1);
    }

    $userId = $conn->real_escape_string($userId);

    $sql = "SELECT Email FROM twoFactorAuth WHERE UserId = '$userId' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['Email'];
        $newPassString = generateRandomPassString();
        $updatePassStringQuery = "UPDATE twoFactorAuth SET PassString = '$newPassString' WHERE UserId = '$userId'";
        if ($conn->query($updatePassStringQuery) === TRUE) {
            $subject = "Verification Code";
            if (sendEmail($email, $subject, $newPassString)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function generateRandomPassString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $passString = '';
    $charactersLength = strlen($characters);

    for ($i = 0; $i < $length; $i++) {
        $passString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $passString;
}


function sendEmail($to, $subject, $authCode) {
    $from = 'help@goodEats.com';
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message = "Your verification code is: " . $authCode;
    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}

function twoFactorAuthenticate($userId, $authCode){
    global $dbHost, $dbUsername, $dbPassword, $dbName;

    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        return array('returnCode' => -1, 'message' => 'Error connecting to the database');
    }

    $userId = $conn->real_escape_string($userId);
    $authCode = $conn->real_escape_string($authCode);

    $sql = "SELECT PassString FROM twoFactorAuth WHERE UserId = '$userId' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $passString = $row['PassString'];

        if ($authCode === $passString) {
            $updateTimeSql = "UPDATE twoFactorAuth SET LastAuthDateTime = NOW() WHERE UserId = '$userId'";
            if ($conn->query($updateTimeSql) === TRUE) {
                $conn->close();
                return array('returnCode' => 1, 'message' => 'Authentication successful');
            } else {
                $conn->close();
                return array('returnCode' => 0, 'message' => 'Failed to update LastAuthDateTime');
            }
        } else {
            $conn->close();
            return array('returnCode' => 0, 'message' => 'Authentication failed');
        }
    } else {
        $conn->close();
        return array('returnCode' => 0, 'message' => 'User ID not found or other error');
    }
}

function getRandomUnratedMeal($username) {
    global $dbHost, $dbUsername, $dbPassword, $dbName;
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT mealID, rating FROM ratings WHERE accountID = ? AND rating >= 3";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ratedMeals = [];
    while ($row = $result->fetch_assoc()) {
        $ratedMeals[$row['mealID']] = $row['rating'];
    }

    $categoriesAndAreas = [];
    foreach ($ratedMeals as $mealID => $rating) {
        $query = "SELECT strCategory, strArea FROM custom_meals WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $mealID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $categoriesAndAreas[] = [
                'strCategory' => $row['strCategory'],
                'strArea' => $row['strArea'],
            ];
        } else {
            $apiUrl = "https://www.themealdb.com/api/json/v1/1/lookup.php?i={$mealID}";
            $apiData = file_get_contents($apiUrl);
            $apiData = json_decode($apiData, true);
            if ($apiData['meals'][0]) {
                $categoriesAndAreas[] = [
                    'strCategory' => $apiData['meals'][0]['strCategory'],
                    'strArea' => $apiData['meals'][0]['strArea'],
                ];
            }
        }
    }

    $uniqueCategoriesAndAreas = array_unique($categoriesAndAreas, SORT_REGULAR);

    $randomCategoryArea = $uniqueCategoriesAndAreas[array_rand($uniqueCategoriesAndAreas)];

    $apiCategoryEndpoint = "https://www.themealdb.com/api/json/v1/1/filter.php?c=" . urlencode($randomCategoryArea['strCategory']);
    $apiAreaEndpoint = "https://www.themealdb.com/api/json/v1/1/filter.php?a=" . urlencode($randomCategoryArea['strArea']);
    
    $apiCategoryData = file_get_contents($apiCategoryEndpoint);
    $apiAreaData = file_get_contents($apiAreaEndpoint);
    
    $categoryMeals = json_decode($apiCategoryData, true)['meals'];
    $areaMeals = json_decode($apiAreaData, true)['meals'];
    
    if (!empty($categoryMeals) && !empty($areaMeals)) {
        $overlappingMealIDs = array_column($categoryMeals, 'idMeal');
        
        foreach ($overlappingMealIDs as $mealID) {
            if (!array_key_exists($mealID, $ratedMeals)) {
                $apiUrl = "https://www.themealdb.com/api/json/v1/1/lookup.php?i={$mealID}";
                $apiData = file_get_contents($apiUrl);
                $apiData = json_decode($apiData, true);
                if ($apiData['meals'][0]) {
                    $randomUnratedMeal = $apiData['meals'][0];
                    break;
                }
            }
        }
    }
    
    if (empty($overlappingMeals) || !isset($randomUnratedMeal)) {
        $apiRandomCategoryEndpoint = "https://www.themealdb.com/api/json/v1/1/filter.php?c=" . urlencode($randomCategoryArea['strCategory']);
        $apiRandomCategoryData = file_get_contents($apiRandomCategoryEndpoint);
        $randomCategoryMeals = json_decode($apiRandomCategoryData, true)['meals'];
        
        $unratedCategoryMeals = array_filter($randomCategoryMeals, function ($meal) use ($ratedMeals) {
            return !array_key_exists($meal['idMeal'], $ratedMeals);
        });
        
        $randomUnratedMeal = $unratedCategoryMeals[array_rand($unratedCategoryMeals)];
    }

    $conn->close();

    return $randomUnratedMeal;
}

function saveComment($mealID, $username, $content)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;

    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        return array("returnCode" => 0, "message" => "Error connecting to the database: " . $conn->connect_error);
    }

    $sql = "INSERT INTO discussion (MealID, Username, PostDate, Content) VALUES (?, ?, NOW(), ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $mealID, $username, $content);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return array("returnCode" => 1, "message" => "Comment added successfully.");
    } else {
        $stmt->close();
        $conn->close();
        return array("returnCode" => 0, "message" => "Error: " . $sql . "<br>" . $conn->error);
    }
}

function getComments($mealID)
{
    global $dbHost, $dbUsername, $dbPassword, $dbName;

    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        return array("returnCode" => 0, "message" => "Error connecting to the database: " . $conn->connect_error);
    }

    $sql = "SELECT Username, PostDate, Content FROM discussion WHERE MealID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mealID);
    
    $stmt->execute();
    if ($stmt->error) {
        error_log("SQL error: " . $stmt->error);
        return array("returnCode" => 0, "message" => "SQL error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        error_log("No result obtained from the query.");
        return array("returnCode" => 0, "message" => "No result obtained from the query.");
    }
    
    $comments = array();
    
    while ($row = $result->fetch_assoc()) {
        $comments[] = array(
            "username" => $row['Username'],
            "postDate" => $row['PostDate'],
            "content" => $row['Content']
        );
    }
    
    $stmt->close();
    $conn->close();
    
    return array("returnCode" => 1, "comments" => $comments);
}



?>