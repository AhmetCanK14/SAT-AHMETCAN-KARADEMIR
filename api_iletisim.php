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

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$message = trim($data['message'] ?? '');

// Validasyon
if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Tüm alanlar doldurulmalıdır']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Geçersiz e-posta adresi']);
    exit;
}

// Veritabanına kaydet
try {
    $stmt = $pdo->prepare('INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $message]);
    $contact_id = (int)$pdo->lastInsertId();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Veritabanı hatası']);
    exit;
}

echo json_encode([
    'success' => true, 
    'id' => $contact_id,
    'message' => 'Mesajınız başarıyla kaydedildi. Admin panelinde görülebilir.'
]);
?>
