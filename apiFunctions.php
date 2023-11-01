<?php
function searchMeals($query) {
    $apiEndpoint = "https://www.themealdb.com/api/json/v1/1/search.php?s=" . urlencode($query);

    $ch = curl_init($apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($response === false) {
        die('cURL Error: ' . curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);
    $meals = $data['meals'];
    $formattedMeals = [];
    if (!empty($meals)) {
        foreach ($meals as $meal) {
            $formattedMeals[] = [
                'idMeal' => $meal['idMeal'],
                'strMeal' => $meal['strMeal'],
                'strArea' => $meal['strArea'],
                'strMealThumb' => $meal['strMealThumb']
            ];
        }
    }
    return array('returnCode' => 1, 'message' => $formattedMeals);
}

function getMealDetails($query){
    
    $apiEndpoint = "https://www.themealdb.com/api/json/v1/1/lookup.php?i=" . $query;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return array("returnCode" => 0, "message" => "Curl error: " . curl_error($ch));
    }
    curl_close($ch);

    $mealData = json_decode($response, true);

    if ($mealData === null) {
        return array("returnCode" => 0, "message" => "Unable to decode the JSON data.");
    }

    if (isset($mealData['meals'][0])) {
        return array("returnCode" => 1, "message" => $mealData['meals'][0]);
    } else {
        return array("returnCode" => 0, "message" => "Error Retrieving data from the API.");
    }
}

function populateFields($query) {
    if ($query == 'category') {
        $apiEndpoint = 'https://www.themealdb.com/api/json/v1/1/list.php?c=list';
        $dataKey = "strCategory";
    } elseif ($query == 'area') {
        $apiEndpoint = 'https://www.themealdb.com/api/json/v1/1/list.php?a=list';
        $dataKey = "strArea";
    } else {
        return array("returnCode" => 0, "message" => "Error parsing query parameter.");
    }

    $ch = curl_init($apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($response === false) {
        die('cURL Error: ' . curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if ($data === null) {
        die('JSON Error: ' . json_last_error_msg());
    }

    if (isset($data["meals"])) {
        $values = array();
        foreach ($data["meals"] as $meal) {
            $values[] = $meal[$dataKey];
        }

        return array("returnCode" => 1, "message" => $values);
    } else {
        return array("returnCode" => 0, "message" => "Data not found in the JSON response.");
    }
}

?>
