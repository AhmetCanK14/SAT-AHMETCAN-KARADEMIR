<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/db.php';

// Sadece POST kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = $_POST;

$customer_name = trim($data['customer_name'] ?? '');
$phone = trim($data['phone'] ?? null);
$email = trim($data['email'] ?? null);
$reservation_date = trim($data['reservation_date'] ?? '');
$reservation_time = trim($data['reservation_time'] ?? '');
$people = intval($data['people'] ?? 1);
$notes = trim($data['notes'] ?? null);

if ($customer_name === '' || $reservation_date === '' || $reservation_time === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Eksik alanlar']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO reservations (customer_name, phone, email, reservation_date, reservation_time, people, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$customer_name, $phone, $email, $reservation_date, $reservation_time, $people, $notes]);
    $id = (int)$pdo->lastInsertId();
    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB insert error']);
}
