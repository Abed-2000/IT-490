<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


if ($_POST){
    $request = array();
    $request['type'] = $_POST["type"];
    $request['username'] = $_POST["username"];
    $request['password'] = $_POST["password"];
    $request['email'] = $_POST["email"];
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response !== null) {
        echo json_encode($response);
    }
}
?>
