<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "<h1>Thêm dữ liệu mẫu cho bảng san_pham</h1>";
    
    // Kiểm tra xem bảng san_pham có tồn tại không
    $stmt = $conn->query("SHOW TABLES LIKE 'san_pham'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>⚠️ Bảng 'san_pham' không tồn tại. Vui lòng tạo bảng trước.</p>";
        echo "<p>Cấu trúc bảng cần có: id_sanpham, ten_sanpham, gia, hinh_anh, mau_sac, so_luong, mo_ta, id_danhmuc, created_at, update_at, ma_km</p>";
        exit;
    }
    
    // Kiểm tra cấu trúc bảng
    echo "<h2>Cấu trúc bảng san_pham:</h2>";
    $stmt = $conn->query("DESCRIBE san_pham");
    $columns = $stmt->fetchAll();
    echo "<ul>";
    foreach($columns as $column) {
        echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
    }
    echo "</ul>";
    
    // Kiểm tra dữ liệu hiện có
    $stmt = $conn->query("SELECT COUNT(*) as total FROM san_pham");
    $result = $stmt->fetch();
    echo "<p>Hiện tại có <strong>" . $result['total'] . "</strong> sản phẩm trong database.</p>";
    
    // Thêm dữ liệu mẫu nếu chưa có
    if ($result['total'] == 0) {
        echo "<h2>Đang thêm dữ liệu mẫu...</h2>";
        
        $products = [
            [
                'ten_sanpham' => 'Kindle Paperwhite 11th Gen',
                'gia' => 2999000,
                'hinh_anh' => 'img/products/f1.jpg',
                'mau_sac' => 'Đen',
                'so_luong' => 50,
                'mo_ta' => 'Kindle Paperwhite thế hệ 11 với màn hình 6.8 inch chống chói, độ phân giải 300 ppi',
                'id_danhmuc' => 1,
                'ma_km' => 'KM001'
            ],
            [
                'ten_sanpham' => 'Boox Note Air 3',
                'gia' => 12999000,
                'hinh_anh' => 'img/products/f2.jpg',
                'mau_sac' => 'Trắng',
                'so_luong' => 25,
                'mo_ta' => 'Máy đọc sách Boox Note Air 3 với màn hình E-ink 10.3 inch, hỗ trợ viết tay',
                'id_danhmuc' => 2,
                'ma_km' => 'KM002'
            ],
            [
                'ten_sanpham' => 'Kobo Clara 2E',
                'gia' => 4499000,
                'hinh_anh' => 'img/products/f3.jpg',
                'mau_sac' => 'Xanh',
                'so_luong' => 40,
                'mo_ta' => 'Kobo Clara 2E thân thiện với môi trường, màn hình 6 inch HD',
                'id_danhmuc' => 3,
                'ma_km' => 'KM003'
            ],
            [
                'ten_sanpham' => 'PocketBook Touch HD 3',
                'gia' => 5299000,
                'hinh_anh' => 'img/products/f4.jpg',
                'mau_sac' => 'Đồng',
                'so_luong' => 30,
                'mo_ta' => 'PocketBook Touch HD 3 với màn hình cảm ứng 6 inch độ phân giải cao',
                'id_danhmuc' => 4,
                'ma_km' => 'KM004'
            ],
            [
                'ten_sanpham' => 'Kindle Oasis',
                'gia' => 6999000,
                'hinh_anh' => 'img/products/n1.jpg',
                'mau_sac' => 'Graphite',
                'so_luong' => 15,
                'mo_ta' => 'Kindle Oasis cao cấp với nút lật trang vật lý và màn hình 7 inch',
                'id_danhmuc' => 1,
                'ma_km' => 'KM005'
            ],
            [
                'ten_sanpham' => 'Boox Poke 5',
                'gia' => 4999000,
                'hinh_anh' => 'img/products/n2.jpg',
                'mau_sac' => 'Đen',
                'so_luong' => 35,
                'mo_ta' => 'Boox Poke 5 nhỏ gọn với màn hình 6 inch, Android 11',
                'id_danhmuc' => 2,
                'ma_km' => null
            ],
            [
                'ten_sanpham' => 'Kobo Sage',
                'gia' => 9600000,
                'hinh_anh' => 'img/products/n3.jpg',
                'mau_sac' => 'Đen',
                'so_luong' => 20,
                'mo_ta' => 'Kobo Sage với màn hình lớn 8 inch và hỗ trợ Kobo Stylus',
                'id_danhmuc' => 3,
                'ma_km' => 'KM006'
            ],
            [
                'ten_sanpham' => 'Kindle Scribe',
                'gia' => 12400000,
                'hinh_anh' => 'img/products/n4.jpg',
                'mau_sac' => 'Tungsten',
                'so_luong' => 10,
                'mo_ta' => 'Kindle Scribe với khả năng viết và vẽ, màn hình 10.2 inch',
                'id_danhmuc' => 1,
                'ma_km' => 'KM007'
            ]
        ];
        
        $stmt = $conn->prepare("INSERT INTO san_pham (ten_sanpham, gia, hinh_anh, mau_sac, so_luong, mo_ta, id_danhmuc, ma_km, created_at, update_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $added = 0;
        foreach ($products as $product) {
            try {
                if ($stmt->execute([
                    $product['ten_sanpham'],
                    $product['gia'],
                    $product['hinh_anh'],
                    $product['mau_sac'],
                    $product['so_luong'],
                    $product['mo_ta'],
                    $product['id_danhmuc'],
                    $product['ma_km']
                ])) {
                    $added++;
                    echo "<p style='color: green;'>✓ Thêm thành công: {$product['ten_sanpham']}</p>";
                }
            } catch(Exception $e) {
                echo "<p style='color: red;'>✗ Lỗi thêm {$product['ten_sanpham']}: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<h3>Kết quả: Đã thêm $added/" . count($products) . " sản phẩm!</h3>";
    }
    
    // Hiển thị sản phẩm hiện có
    echo "<h2>Sản phẩm trong database:</h2>";
    $stmt = $conn->query("SELECT * FROM san_pham ORDER BY created_at DESC LIMIT 10");
    $products = $stmt->fetchAll();
    
    if (!empty($products)) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Tên sản phẩm</th><th>Giá</th><th>Màu sắc</th><th>Số lượng</th><th>Mã KM</th><th>Hình ảnh</th>";
        echo "</tr>";
        
        foreach($products as $product) {
            echo "<tr>";
            echo "<td>" . $product['id_sanpham'] . "</td>";
            echo "<td>" . htmlspecialchars($product['ten_sanpham']) . "</td>";
            echo "<td>" . number_format($product['gia'], 0, ',', '.') . "đ</td>";
            echo "<td>" . htmlspecialchars($product['mau_sac']) . "</td>";
            echo "<td>" . $product['so_luong'] . "</td>";
            echo "<td>" . ($product['ma_km'] ? htmlspecialchars($product['ma_km']) : '-') . "</td>";
            echo "<td><img src='" . htmlspecialchars($product['hinh_anh']) . "' width='50' height='50' style='object-fit: cover;' onerror=\"this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22%3E%3Crect width=%22100%25%22 height=%22100%25%22 fill=%22%23ddd%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22%3ENo Image%3C/text%3E%3C/svg%3E'\"></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>

<hr>
<p><a href="index.php">→ Xem trang chủ</a></p>
<style>
    body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
    table { margin: 20px 0; }
    th, td { padding: 8px 12px; text-align: left; }
    th { background-color: #f8f9fa; }
</style>