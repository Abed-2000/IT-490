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

    function saveRecipe($formDataArray) {

        global $dbHost, $dbUsername, $dbPassword, $dbName;
        $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
    
        if ($conn->connect_error) {
            echo "Error connecting to database: " . $conn->connect_error . PHP_EOL;
            exit(1);
        }

        foreach ($formDataArray as $formData) {
            $sql = "INSERT INTO custom_meals (strMeal, strCategory, strArea, strInstructions, strMealThumb, strTags, 
                strIngredient1, strIngredient2, strIngredient3, strIngredient4, strIngredient5, strIngredient6, strIngredient7, 
                strIngredient8, strIngredient9, strIngredient10, strIngredient11, strIngredient12, strIngredient13, strIngredient14, 
                strIngredient15, strIngredient16, strIngredient17, strIngredient18, strIngredient19, strIngredient20, strMeasure1, 
                strMeasure2, strMeasure3, strMeasure4, strMeasure5, strMeasure6, strMeasure7, strMeasure8, strMeasure9, strMeasure10, 
                strMeasure11, strMeasure12, strMeasure13, strMeasure14, strMeasure15, strMeasure16, strMeasure17, strMeasure18, 
                strMeasure19, strMeasure20, strYoutube, datePublished, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss",
                $formData['strMeal'], $formData['strCategory'], $formData['strArea'], $formData['strInstructions'], $formData['strMealThumb'], $formData['strTags'],
                $formData['strIngredients'][0], $formData['strIngredients'][1], $formData['strIngredients'][2], $formData['strIngredients'][3], $formData['strIngredients'][4],
                $formData['strIngredients'][5], $formData['strIngredients'][6], $formData['strIngredients'][7], $formData['strIngredients'][8], $formData['strIngredients'][9],
                $formData['strIngredients'][10], $formData['strIngredients'][11], $formData['strIngredients'][12], $formData['strIngredients'][13], $formData['strIngredients'][14],
                $formData['strIngredients'][15], $formData['strIngredients'][16], $formData['strIngredients'][17], $formData['strIngredients'][18], $formData['strIngredients'][19],
                $formData['strMeasure'][0], $formData['strMeasure'][1], $formData['strMeasure'][2], $formData['strMeasure'][3], $formData['strMeasure'][4],
                $formData['strMeasure'][5], $formData['strMeasure'][6], $formData['strMeasure'][7], $formData['strMeasure'][8], $formData['strMeasure'][9],
                $formData['strMeasure'][10], $formData['strMeasure'][11], $formData['strMeasure'][12], $formData['strMeasure'][13], $formData['strMeasure'][14],
                $formData['strMeasure'][15], $formData['strMeasure'][16], $formData['strMeasure'][17], $formData['strMeasure'][18], $formData['strMeasure'][19],
                $formData['strYoutube'], $formData['datePublished'], $formData['created_by']);
    
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
        return array("returnCode" => 1, "message" => "Meal was successfully saved.");
    }