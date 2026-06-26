<?php
namespace app\services;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPublisher {
    public function publish(string $routingKey, array $data): void {
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $port = 5672;
        $user = 'admin'; 
        $pass = 'test1234';

        try {
            $connection = new AMQPStreamConnection($host, $port, $user, $pass);
            $channel = $connection->channel();

            $channel->exchange_declare('city.events', 'topic', false, true, false);

            $msg = new AMQPMessage(
                json_encode($data),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            $channel->basic_publish($msg, 'city.events', $routingKey);

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            error_log("RabbitMQ Error: " . $e->getMessage());
        }
    }
}