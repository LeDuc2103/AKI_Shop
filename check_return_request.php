<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== KIỂM TRA YÊU CẦU ĐỔI TRẢ ĐƠN HÀNG #22 ===\n\n";

// Kiểm tra trong bảng don_hang_doi_tra
$stmt = $conn->prepare('SELECT * FROM don_hang_doi_tra WHERE ma_donhang = 22');
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo "✓ Tìm thấy yêu cầu đổi trả:\n";
    echo "  - ID: " . $result['id'] . "\n";
    echo "  - Mã đơn hàng: " . $result['ma_donhang'] . "\n";
    echo "  - Mã user: " . $result['ma_user'] . "\n";
    echo "  - Trạng thái: " . $result['status'] . "\n";
    echo "  - Lý do: " . $result['ly_do'] . "\n";
    echo "  - Ngày tạo: " . $result['created_at'] . "\n";
    echo "  - Ngày cập nhật: " . $result['updated_at'] . "\n";
} else {
    echo "✗ KHÔNG tìm thấy yêu cầu đổi trả cho đơn #22\n";
}

echo "\n=== KIỂM TRA THÔNG TIN ĐƠN HÀNG #22 ===\n\n";
$orderStmt = $conn->prepare('SELECT ma_donhang, ma_user, trang_thai, trangthai_thanhtoan FROM don_hang WHERE ma_donhang = 22');
$orderStmt->execute();
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);

if ($order) {
    echo "✓ Thông tin đơn hàng:\n";
    echo "  - Mã đơn: " . $order['ma_donhang'] . "\n";
    echo "  - Mã user: " . $order['ma_user'] . "\n";
    echo "  - Trạng thái: " . $order['trang_thai'] . "\n";
    echo "  - Thanh toán: " . $order['trangthai_thanhtoan'] . "\n";
}
?>
