<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tuc');

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
            
            // Đảm bảo UTF-8 cho WAMP cũ
            $this->connection->exec("SET NAMES utf8 COLLATE utf8_unicode_ci");
            $this->connection->exec("SET CHARACTER SET utf8");
            $this->connection->exec("SET character_set_connection=utf8");
            
        } catch(PDOException $e) {
            die("Lỗi kết nối database: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Thêm dữ liệu demo cho bảng user
   /* public function insertDemoData() {
        try {
            // Kiểm tra xem đã có dữ liệu demo chưa
            $checkSql = "SELECT COUNT(*) FROM user WHERE email = 'admin@kltn.com'";
            $stmt = $this->connection->prepare($checkSql);
            $stmt->execute();
            
            if ($stmt->fetchColumn() == 0) {
                // Thêm admin mặc định
                $sql = "INSERT INTO user (ma_user, ho_ten, email, password, phone, dia_chi, vai_tro, trang_thai, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->execute(array(
                    1,
                    'Administrator',
                    'admin@kltn.com',
                    md5('123456'),
                    '0123456789',
                    'TP.HCM',
                    'admin',
                    'active'
                ));
                
                // Thêm nhân viên demo
                $stmt->execute(array(
                    2,
                    'Lê Văn Túc',
                    'tuc@kltn.com',
                    md5('123456'),
                    '0987654321',
                    'TP.HCM',
                    'nhanvien',
                    'active'
                ));
                
                // Thêm user demo
                $stmt->execute(array(
                    3,
                    'Nguyễn Văn User',
                    'user@kltn.com',
                    md5('123456'),
                    '0111222333',
                    'TP.HCM',
                    'user',
                    'active'
                ));
                
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Lỗi thêm dữ liệu demo: " . $e->getMessage());
            return false;
        }
    } */
}

// Khởi tạo database và thêm dữ liệu demo
try {
    $db = new Database();
   //$db->insertDemoData();
} catch(Exception $e) {
    error_log("Lỗi khởi tạo database: " . $e->getMessage());
} 
?>
