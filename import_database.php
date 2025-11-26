<?php
// File để import database từ kltn.sql
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Thông tin kết nối database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kltn';

try {
    // Kết nối MySQL (không chọn database trước)
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tạo database nếu chưa tồn tại
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    echo "✅ Database '$database' đã được tạo/kiểm tra<br>";
    
    // Chọn database
    $pdo->exec("USE `$database`");
    
    // Đọc file SQL
    $sqlFile = 'kltn.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Không tìm thấy file $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Tách các câu lệnh SQL
    $statements = explode(';', $sql);
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Bỏ qua câu lệnh rỗng và comment
        if (empty($statement) || 
            strpos($statement, '--') === 0 || 
            strpos($statement, '/*') === 0 ||
            strpos($statement, '/*!') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            $errorCount++;
            echo "⚠️ Lỗi: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br>📊 <strong>Kết quả import:</strong><br>";
    echo "✅ Câu lệnh thành công: $successCount<br>";
    echo "❌ Câu lệnh lỗi: $errorCount<br>";
    
    // Kiểm tra các bảng đã được tạo
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<br>📋 <strong>Các bảng đã tạo:</strong><br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
    
    echo "<br>🎉 <strong>Import database hoàn tất!</strong>";
    
} catch (Exception $e) {
    echo "❌ <strong>Lỗi:</strong> " . $e->getMessage();
}
?>