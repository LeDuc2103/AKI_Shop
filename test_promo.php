<?php
// Script test để kiểm tra mã khuyến mãi
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Danh sách mã khuyến mãi trong database:</h2>";

try {
    $stmt = $conn->query("SELECT * FROM khuyen_mai");
    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($promos)) {
        echo "<p style='color: red;'>Không có mã khuyến mãi nào trong database!</p>";
        echo "<p>Hãy thêm mã khuyến mãi bằng query sau:</p>";
        echo "<pre>";
        echo "INSERT INTO khuyen_mai (ten_km, phan_tram_km, ngay_bat_dau, ngay_ket_thuc, so_luong_toi_da, so_luong_su_dung) 
VALUES ('GIAM10', 10, '2025-12-01', '2025-12-31', 100, 0);

INSERT INTO khuyen_mai (ten_km, phan_tram_km, ngay_bat_dau, ngay_ket_thuc, so_luong_toi_da, so_luong_su_dung) 
VALUES ('GIAM20', 20, '2025-12-01', '2025-12-31', 50, 0);
";
        echo "</pre>";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Tên KM</th><th>Phần trăm</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Số lượng tối đa</th><th>Đã sử dụng</th></tr>";
        
        foreach ($promos as $promo) {
            echo "<tr>";
            echo "<td>" . $promo['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($promo['ten_km']) . "</strong></td>";
            echo "<td>" . $promo['phan_tram_km'] . "%</td>";
            echo "<td>" . $promo['ngay_bat_dau'] . "</td>";
            echo "<td>" . $promo['ngay_ket_thuc'] . "</td>";
            echo "<td>" . $promo['so_luong_toi_da'] . "</td>";
            echo "<td>" . $promo['so_luong_su_dung'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<br><p style='color: green;'>✓ Có " . count($promos) . " mã khuyến mãi trong database</p>";
        echo "<p><strong>Hướng dẫn test:</strong></p>";
        echo "<ol>";
        echo "<li>Thêm sản phẩm vào giỏ hàng</li>";
        echo "<li>Vào trang giỏ hàng (cart.php)</li>";
        echo "<li>Nhập một trong các mã trên vào ô 'Nhập mã giảm giá'</li>";
        echo "<li>Nhấn nút 'Áp dụng'</li>";
        echo "</ol>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>
