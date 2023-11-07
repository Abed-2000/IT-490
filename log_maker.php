<?php
require_once('rabbitMQLib.inc');

function log_this($log_msg)
{
    $log_time = date('m-d-Y h:i:sa');
    $log_filename = 'rabbit';
    if (!file_exists($log_filename)) 
    {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename . '.log';


    $client = new rabbitMQClient("testRabbitMQ.ini", "logger");
    $response = $client->publish($log_msg);

    $reply = $client->subscribe("hi");
    file_put_contents($log_file_data, $log_time. "\n" . $response. "\n\n", FILE_APPEND);


    echo $response . "space" . $reply;

}
?>
