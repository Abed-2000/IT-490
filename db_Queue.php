#!/usr/bin/php
<?php

function sendToDbQueue($request)
{
    // RabbitMQ server settings
    $host = 'localhost';
    $port = 5672;
    $user = 'guest';
    $pass = 'guest';
    $queueName = 'database_queue';

    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $connection->channel();

    // Declare the queue
    $channel->queue_declare($queueName, false, true, false, false);

    // Create an AMQPMessage with the data you want to send to the database
    $messageData = json_encode($request['data']); // Assuming data is in an array
    $message = new AMQPMessage($messageData);

    // Publish the message to the database queue
    $channel->basic_publish($message, '', $queueName);

    echo "Sent message to the database queue: $messageData\n";

    // Clean up
    $channel->close();
    $connection->close();

    return "Message sent to the database queue";
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

$server->process_requests('requestProcessor');
exit();
?>

