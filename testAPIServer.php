#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('apiFunctions.php');

function requestProcessor($request)
{
    echo "received request" . PHP_EOL;
    var_dump($request);
    switch ($request['type']) 
    {
        case "searchMeals":
            if (isset($request['query'])) {
                $response = searchMeals($request['query']);
                var_dump($response);
                return $response;
            } else {
                return ['returnCode' => 0, 'message' => 'Query parameter is missing'];
            }
        case "mealDetails":
            if (isset($request['query'])) {
                $response = getMealDetails($request['query']);
                var_dump($response);
                return $response;
            } else {
                return ['returnCode' => 0, 'message' => 'Query parameter is missing'];
            }
                case "populateFields":
                    if (isset($request['query'])) {
                        $response = populateFields($request['query']);
                        var_dump($response);
                        return $response;
                    } else {
                        return ['returnCode' => 0, 'message' => 'Query parameter is missing'];
                    }
    }
}

$server = new rabbitMQServer("testRabbitMQ.ini","api");

echo "api server started up" . PHP_EOL;
$server -> process_requests('requestProcessor');
echo "api server shut down" . PHP_EOL;

exit();
?>
