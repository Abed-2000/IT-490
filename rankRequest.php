<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


if ($_POST){
    $request = array();
    $request['type'] = $_POST["type"];
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response !== null) {
        echo json_encode($response, JSON_UNESCAPED_SLASHES);
    }
}
?>