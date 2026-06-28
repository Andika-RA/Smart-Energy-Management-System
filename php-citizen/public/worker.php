<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use app\Database;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$port = (int)(getenv('RABBITMQ_PORT') ?: 5672);
$user = getenv('RABBITMQ_USER') ?: 'guest';
$pass = getenv('RABBITMQ_PASS') ?: 'guest';
sleep(10);

try {
    $conn = new AMQPStreamConnection($host, $port, $user, $pass);
    $channel = $conn->channel();

    $channel->exchange_declare('city.events', 'topic', false, true, false);

    list($queue_name, ,) = $channel->queue_declare('', false, false, true, false);
    $channel->queue_bind($queue_name, 'city.events', 'anomaly.alert');
    $channel->queue_bind($queue_name, 'city.events', 'report.submitted');

    echo " [*] Citizen Service memantau anomaly.alert & report.submitted. Tekan CTRL+C untuk keluar\n";

    $callback = function ($msg) {
        $routingKey = $msg->delivery_info['routing_key'] ?? '';
        $data = json_decode($msg->body, true);

        if ($routingKey === 'report.submitted') {
            echo " [!] Event Laporan Baru Diterima dari RabbitMQ: " . $msg->body . "\n";
        } else {
            echo " [!] Anomali Diterima: " . $msg->body . "\n";

            $db = (new Database())->getConnection();

            $zone_id = $data['zone_id'] ?? 1;

            $stmt = $db->prepare("SELECT id FROM citizen_citizens WHERE zone_id = ?");
            $stmt->execute([$zone_id]);
            $citizens = $stmt->fetchAll();

            if (count($citizens) > 0) {
                $insertStmt = $db->prepare("INSERT INTO citizen_notifications (citizen_id, is_broadcast, title, body, is_read, created_at) VALUES (?, 0, ?, ?, 0, ?)");
                $now = date('Y-m-d H:i:s');
                $body = "Peringatan! Terdeteksi anomali sistem (Skor: {$data['anomaly_score']}) di zona Anda.";

                foreach ($citizens as $c) {
                    $insertStmt->execute([$c['id'], "Peringatan Anomali Kota", $body, $now]);
                }
                echo " [v] Sukses membuat " . count($citizens) . " notifikasi untuk warga Zona $zone_id.\n";
            }
        }
        $msg->ack();
    };

    $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

    while ($channel->is_open()) {
        $channel->wait();
    }
} catch (\Exception $e) {
    echo "RabbitMQ Error: " . $e->getMessage() . "\n";
}
