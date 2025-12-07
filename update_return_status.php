<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== CẬP NHẬT TRẠNG THÁI ĐỔI TRẢ ĐƠN #22 ===\n\n";

$stmt = $conn->prepare('UPDATE don_hang_doi_tra SET status = ?, updated_at = NOW() WHERE ma_donhang = 22');
$result = $stmt->execute(array('approved'));

if ($result) {
    echo "✓ Đã cập nhật trạng thái thành 'approved'\n\n";
    
    // Kiểm tra lại
    $check = $conn->prepare('SELECT * FROM don_hang_doi_tra WHERE ma_donhang = 22');
    $check->execute();
    $data = $check->fetch(PDO::FETCH_ASSOC);
    
    echo "Trạng thái hiện tại:\n";
    echo "  - Status: " . $data['status'] . "\n";
    echo "  - Updated at: " . $data['updated_at'] . "\n";
} else {
    echo "✗ Cập nhật thất bại\n";
}
?>
