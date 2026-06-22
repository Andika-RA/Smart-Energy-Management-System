<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/database.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Database;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
sleep(10); 

try {
    $conn = new AMQPStreamConnection($host, 5672, 'guest', 'guest');
    $channel = $conn->channel();

    $channel->exchange_declare('city.events', 'topic', false, true, false);
    
    list($queue_name, ,) = $channel->queue_declare('', false, false, true, false);
    $channel->queue_bind($queue_name, 'city.events', 'anomaly.alert');

    echo " [*] Citizen Service memantau anomaly.alert. Tekan CTRL+C untuk keluar\n";

    $callback = function ($msg) {
        $data = json_decode($msg->body, true);
        echo " [!] Anomali Diterima: " . $msg->body . "\n";
        
        $db = (new Database())->getConnection();
        
        $zone_id = $data['zone_id'] ?? 1; 
        
        $stmt = $db->prepare("SELECT id FROM citizen_citizens WHERE zone_id = ?");
        $stmt->execute([$zone_id]);
        $citizens = $stmt->fetchAll();
        
        // Notification
        if (count($citizens) > 0) {
            $insertStmt = $db->prepare("INSERT INTO citizen_notifications (citizen_id, title, body, is_read, created_at) VALUES (?, ?, ?, 0, ?)");
            $now = date('Y-m-d H:i:s');
            $body = "Peringatan! Terdeteksi anomali sistem (Skor: {$data['anomaly_score']}) di zona Anda.";
            
            foreach ($citizens as $c) {
                $insertStmt->execute([$c['id'], "Peringatan Anomali Kota", $body, $now]);
            }
            echo " [v] Sukses membuat " . count($citizens) . " notifikasi untuk warga Zona $zone_id.\n";
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