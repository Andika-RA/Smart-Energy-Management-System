<?php
// app/services/RabbitMQPublisher.php
namespace app\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPublisher {
    public function publish(string $routingKey, array $data): void {
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $port = (int)(getenv('RABBITMQ_PORT') ?: 5672);
        $user = getenv('RABBITMQ_USER') ?: 'guest';
        $pass = getenv('RABBITMQ_PASS') ?: 'guest';

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
        } catch (\Throwable $e) {
            // \Throwable (bukan hanya \Exception) supaya kegagalan publish (mis. koneksi
            // putus, atau library belum ter-load) tidak ikut menggagalkan response API utama.
            error_log("RabbitMQ Error: " . $e->getMessage());
        }
    }
}
