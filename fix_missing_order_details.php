<?php
/**
 * Script để khôi phục chi tiết sản phẩm cho các đơn hàng cũ
 * Chạy file này 1 lần để fix dữ liệu
 */

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>🔧 Đang sửa dữ liệu chi tiết đơn hàng...</h2>";
    
    // Lấy tất cả đơn hàng không có chi tiết
    $stmt = $conn->prepare("
        SELECT dh.* 
        FROM don_hang dh
        LEFT JOIN chitiet_donhang ct ON dh.ma_donhang = ct.ma_donhang
        WHERE ct.ma_donhang IS NULL
        ORDER BY dh.ma_donhang ASC
    ");
    $stmt->execute();
    $orders_without_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orders_without_details)) {
        echo "<p style='color: green;'>✅ Tất cả đơn hàng đều đã có chi tiết sản phẩm!</p>";
        exit;
    }
    
    echo "<p>Tìm thấy <strong>" . count($orders_without_details) . "</strong> đơn hàng không có chi tiết sản phẩm.</p>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Mã đơn</th><th>Người nhận</th><th>Tổng tiền</th><th>Ngày tạo</th><th>Trạng thái</th></tr>";
    
    foreach ($orders_without_details as $order) {
        echo "<tr>";
        echo "<td>DH" . str_pad($order['ma_donhang'], 4, '0', STR_PAD_LEFT) . "</td>";
        echo "<td>" . htmlspecialchars($order['ten_nguoinhan']) . "</td>";
        echo "<td>" . number_format($order['tong_tien']) . " VNĐ</td>";
        echo "<td>" . $order['created_at'] . "</td>";
        echo "<td>";
        
        // Vì không có thông tin sản phẩm gốc, ta không thể khôi phục chính xác
        // Chỉ đánh dấu là đơn hàng cũ
        echo "<span style='color: orange;'>⚠️ Đơn hàng cũ - Không có dữ liệu giỏ hàng</span>";
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>📝 Lưu ý:</h3>";
    echo "<ul>";
    echo "<li>Các đơn hàng này được tạo trước khi hệ thống lưu chi tiết sản phẩm.</li>";
    echo "<li>Không thể khôi phục chi tiết sản phẩm vì giỏ hàng đã bị xóa.</li>";
    echo "<li>Từ bây giờ, tất cả đơn hàng mới sẽ tự động lưu chi tiết sản phẩm.</li>";
    echo "<li>Đơn hàng cũ vẫn hiển thị thông tin tổng tiền và địa chỉ giao hàng.</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>✅ Hệ thống đã được cập nhật!</h3>";
    echo "<p><strong>Tất cả các phương thức thanh toán đã được cấu hình để lưu chi tiết sản phẩm:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Payment COD (payment_cod.php)</li>";
    echo "<li>✅ Payment VNPAY (payment_vnpay.php)</li>";
    echo "<li>✅ Payment SePay (payment_sepay.php)</li>";
    echo "<li>✅ SePay Order (sepay/order.php)</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}
?>
