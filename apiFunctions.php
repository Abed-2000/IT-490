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
    $apiEndpoint = "www.themealdb.com/api/json/v1/1/lookup.php?i=" . urlencode($query);
    $ch = curl_init($apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($response === false) {
        die('cURL Error: ' . curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);
}
?>
