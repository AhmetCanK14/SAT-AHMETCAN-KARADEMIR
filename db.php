<?php
// DB bağlantısı - Hata yönetimi ile
if (!isset($pdo)) {
    $host = '127.0.0.1';
    $db   = 'restoran_db';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    // Önce veritabanı olmadan bağlanmayı dene
    try {
        $dsn_no_db = "mysql:host=$host;charset=$charset";
        $pdo_temp = new PDO($dsn_no_db, $user, $pass);
        $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Veritabanı yoksa oluştur
        $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        
        // Şimdi veritabanı ile bağlan
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        // Tabloları kontrol et ve yoksa oluştur
        $tables = ['users', 'categories', 'menu_items', 'reservations', 'contacts'];
        $pdo->exec("USE `$db`");
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                // Tablo yoksa oluştur
                createTable($pdo, $table);
            }
        }
        
    } catch (PDOException $e) {
        // API istekleri için JSON döndür (api_ prefix'li scriptleri veya Accept header'ı kontrol et)
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        $is_api = (strpos($script_name, 'api_') !== false) ||
                  (strpos($_SERVER['REQUEST_URI'] ?? '', '/api') !== false) ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        if ($is_api) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'DB connection error']);
            exit;
        }
        // HTML sayfaları için hata mesajı
        $error_msg = htmlspecialchars($e->getMessage());
        die("
        <!DOCTYPE html>
        <html lang='tr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Veritabanı Hatası</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #0a0a0c; color: #fff; }
                .card { background: rgba(255,255,255,.1); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,.2); }
                h1 { color: #ef4444; margin-top: 0; }
                .error { color: #ef4444; padding: 15px; background: rgba(239,68,68,.2); border: 1px solid #ef4444; border-radius: 6px; margin: 15px 0; }
                .info { color: #3b82f6; padding: 15px; background: rgba(59,130,246,.2); border: 1px solid #3b82f6; border-radius: 6px; margin: 15px 0; }
                a { color: #8B0000; text-decoration: none; display: inline-block; margin-top: 15px; padding: 10px 20px; background: #8B0000; color: white; border-radius: 6px; }
                a:hover { background: #b00000; }
            </style>
        </head>
        <body>
        <div class='card'>
            <h1>⚠️ Veritabanı Bağlantı Hatası</h1>
            <div class='error'><strong>Hata:</strong> $error_msg</div>
            <div class='info'>
                <strong>Çözüm:</strong><br>
                1. XAMPP Control Panel'den MySQL servisini başlatın<br>
                2. <a href='admin/setup.php'>Kurulum Scriptini Çalıştır</a><br>
                3. Veya <code>db.php</code> dosyasındaki ayarları kontrol edin
            </div>
        </div>
        </body>
        </html>");
    }
}

// Tablo oluşturma fonksiyonu
function createTable($pdo, $table) {
    $sql = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255),
            role VARCHAR(50) DEFAULT 'staff',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'categories' => "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'menu_items' => "CREATE TABLE IF NOT EXISTS menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(8,2) NOT NULL,
            category_id INT DEFAULT NULL,
            available TINYINT(1) DEFAULT 1,
            CONSTRAINT fk_menu_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'reservations' => "CREATE TABLE IF NOT EXISTS reservations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            email VARCHAR(255),
            reservation_date DATE NOT NULL,
            reservation_time TIME NOT NULL,
            people INT DEFAULT 1,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        
        'contacts' => "CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
    
    if (isset($sql[$table])) {
        try {
            $pdo->exec($sql[$table]);
        } catch (PDOException $e) {
            // Hata durumunda sessizce devam et
        }
    }
}
?>