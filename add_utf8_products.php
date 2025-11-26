<?php
// Script để thêm sản phẩm mẫu với tiếng Việt có dấu
header('Content-Type: text/html; charset=UTF-8');

require_once 'config/database.php';

try {
    $conn = $db->getConnection();
    
    // Thiết lập UTF-8 cho WAMP 2.2.0c
    $conn->exec("SET NAMES utf8 COLLATE utf8_unicode_ci");
    $conn->exec("SET CHARACTER SET utf8");
    $conn->exec("SET character_set_connection=utf8");
    
    // Sản phẩm mẫu với tiếng Việt có dấu
    $products = array(
        array(
            'ten_sanpham' => 'Máy đọc sách Kindle Paperwhite',
            'gia' => 2500000,
            'hinh_anh' => 'img/products/scribe2025.jpg',
            'mau_sac' => 'Đen',
            'so_luong' => 15,
            'mo_ta' => 'Máy đọc sách cao cấp với màn hình chống lóa',
            'id_danhmuc' => 1
        ),
        array(
            'ten_sanpham' => 'Máy đọc sách Kobo Clara HD',
            'gia' => 3200000,
            'hinh_anh' => 'img/products/kobo1.jpg',
            'mau_sac' => 'Trắng',
            'so_luong' => 10,
            'mo_ta' => 'Thiết kế nhỏ gọn, hiển thị sắc nét',
            'id_danhmuc' => 1
        ),
        array(
            'ten_sanpham' => 'Máy đọc sách PocketBook Touch HD',
            'gia' => 2800000,
            'hinh_anh' => 'img/products/pocket1.jpg',
            'mau_sac' => 'Xám',
            'so_luong' => 12,
            'mo_ta' => 'Hỗ trợ nhiều định dạng sách điện tử',
            'id_danhmuc' => 1
        )
    );
    
    echo "<h1>Thêm sản phẩm mẫu với UTF-8</h1>";
    
    foreach ($products as $product) {
        // Kiểm tra xem sản phẩm đã tồn tại chưa
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM san_pham WHERE ten_sanpham = ?");
        $check_stmt->execute(array($product['ten_sanpham']));
        
        if ($check_stmt->fetchColumn() == 0) {
            $stmt = $conn->prepare("
                INSERT INTO san_pham (ten_sanpham, gia, hinh_anh, mau_sac, so_luong, mo_ta, id_danhmuc, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute(array(
                $product['ten_sanpham'],
                $product['gia'],
                $product['hinh_anh'],
                $product['mau_sac'],
                $product['so_luong'],
                $product['mo_ta'],
                $product['id_danhmuc']
            ));
            
            if ($result) {
                echo "<p>✓ Đã thêm: " . htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8') . "</p>";
            } else {
                echo "<p>✗ Lỗi thêm: " . htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8') . "</p>";
            }
        } else {
            echo "<p>- Đã tồn tại: " . htmlspecialchars($product['ten_sanpham'], ENT_QUOTES, 'UTF-8') . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>Kiểm tra dữ liệu trong database:</h2>";
    
    $stmt = $conn->prepare("SELECT * FROM san_pham ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "<div style='border: 1px solid #ddd; margin: 10px; padding: 10px;'>";
        echo "<h3>" . htmlspecialchars($row['ten_sanpham'], ENT_QUOTES, 'UTF-8') . "</h3>";
        echo "<p>Giá: " . number_format($row['gia'], 0, ',', '.') . " VNĐ</p>";
        echo "<p>Màu sắc: " . htmlspecialchars($row['mau_sac'], ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p>Mô tả: " . htmlspecialchars($row['mo_ta'], ENT_QUOTES, 'UTF-8') . "</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>