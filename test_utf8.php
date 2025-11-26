<?php
// Test UTF-8 encoding cho WAMP 2.2.0c
header('Content-Type: text/html; charset=UTF-8');

// Kết nối database với UTF-8 settings
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kltn', 'root', '');
    
    // Thiết lập UTF-8 cho WAMP 2.2.0c (MySQL cũ)
    $pdo->exec("SET NAMES utf8 COLLATE utf8_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8");
    $pdo->exec("SET character_set_connection=utf8");
    
    echo "<h1>Test UTF-8 Encoding</h1>";
    echo "<p>Test tiếng Việt: áàảãạăắằẳẵặâấầẩẫậéèẻẽẹêếềểễệ</p>";
    
    // Test database query
    $stmt = $pdo->prepare("SELECT * FROM san_pham LIMIT 3");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Sản phẩm từ database:</h2>";
    foreach ($products as $product) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<h3>" . htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8') . "</h3>";
        echo "<p>Giá: " . number_format($product['gia'], 0, ',', '.') . " VNĐ</p>";
        if ($product['hinh_anh']) {
            echo "<img src='" . htmlspecialchars($product['hinh_anh'], ENT_QUOTES, 'UTF-8') . "' alt='" . htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8') . "' style='max-width: 200px; height: auto;' onerror=\"this.src='img/products/f1.jpg'\">";
        }
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "Lỗi kết nối database: " . $e->getMessage();
}
?>