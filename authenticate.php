<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Function to publish messages to RabbitMQ
function publishToQueue($userData) {
    $host = 'localhost'; // Change this to your RabbitMQ server address
    $port = 5672; // Default port for RabbitMQ
    $user = 'guest'; // Change this to your RabbitMQ user
    $pass = 'guest'; // Change this to your RabbitMQ password
    $queue = 'registration_queue';

    $connection = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $connection->channel();

    $channel->queue_declare($queue, false, false, false, false);

    $msg = new AMQPMessage(json_encode($userData));
    $channel->basic_publish($msg, '', $queue);

    $channel->close();
    $connection->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {
    $request = $_POST;
    $response = "unsupported request type, politely FUCK OFF";

    switch ($request["type"]) {
        case "register":
            if (isset($request['username']) && isset($request['password']) && isset($request['email'])) {
                // Publish user data to RabbitMQ
                publishToQueue([
                    'username' => $request['username'],
                    'password' => $request['password'],
                    'email' => $request['email']
                ]);
                $response = "User data sent for registration";
            } else {
                $response = "Username, password, or email not provided";
            }
            break;
    }

    echo json_encode($response);
    exit(0);
}

?>