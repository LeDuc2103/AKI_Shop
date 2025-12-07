<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>🔍 Kiểm tra phân quyền mới</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #088178; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #088178; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .highlight-admin { background: #d4edda; font-weight: bold; }
        .highlight-customer { background: #fff3cd; }
        .badge { padding: 5px 10px; border-radius: 3px; color: white; font-weight: bold; }
        .badge-quanly { background: #dc3545; }
        .badge-nhanvien { background: #ffc107; color: #000; }
        .badge-khachhang { background: #6c757d; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Kiểm tra phân quyền theo yêu cầu mới</h1>
        <div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <h3 style='color: #0c5460; margin-top: 0;'>📋 Quy tắc phân quyền:</h3>
            <ul style='line-height: 2; font-size: 16px;'>
                <li><strong>quanly</strong> → <span style='background: #28a745; color: white; padding: 3px 8px; border-radius: 3px;'>admin.php</span></li>
                <li><strong>nhanvien</strong> → <span style='background: #28a745; color: white; padding: 3px 8px; border-radius: 3px;'>admin.php</span></li>
                <li><strong>khachhang</strong> → <span style='background: #17a2b8; color: white; padding: 3px 8px; border-radius: 3px;'>index.php</span></li>
            </ul>
        </div>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT ma_user, ho_ten, email, vai_tro, trang_thai FROM user ORDER BY 
                          CASE 
                            WHEN LOWER(vai_tro) = 'quanly' THEN 1
                            WHEN LOWER(vai_tro) = 'nhanvien' THEN 2
                            WHEN LOWER(vai_tro) = 'khachhang' THEN 3
                            ELSE 4
                          END, ma_user");
    
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            <th>Chuyển đến</th>
          </tr>";
    
    $count_quanly = 0;
    $count_nhanvien = 0;
    $count_khachhang = 0;
    $count_other = 0;
    
    while ($row = $stmt->fetch()) {
        $vai_tro = trim($row['vai_tro']);
        $vai_tro_lower = strtolower($vai_tro);
        
        // Phân loại
        $chuyen_den = '';
        $badge_class = 'badge-khachhang';
        $row_class = '';
        
        if ($vai_tro_lower === 'quanly') {
            $chuyen_den = '→ admin.php ✅';
            $badge_class = 'badge-quanly';
            $row_class = 'highlight-admin';
            $count_quanly++;
        } elseif ($vai_tro_lower === 'nhanvien') {
            $chuyen_den = '→ nhanvien.php ✅';
            $badge_class = 'badge-nhanvien';
            $row_class = 'highlight-admin';
            $count_nhanvien++;
        } elseif ($vai_tro_lower === 'khachhang') {
            $chuyen_den = '→ index.php';
            $badge_class = 'badge-khachhang';
            $row_class = 'highlight-customer';
            $count_khachhang++;
        } else {
            $chuyen_den = '→ index.php (vai trò không xác định)';
            $count_other++;
        }
        
        echo "<tr class='" . $row_class . "'>";
        echo "<td><strong>" . $row['ma_user'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['ho_ten']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['email']) . "</strong></td>";
        echo "<td><span class='badge " . $badge_class . "'>" . htmlspecialchars($vai_tro) . "</span></td>";
        echo "<td>" . ($row['trang_thai'] == 'active' ? '✅ Active' : '❌ Inactive') . "</td>";
        echo "<td><strong>" . $chuyen_den . "</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>
        <h3 style='color: #155724; margin-top: 0;'>📊 Thống kê:</h3>
        <ul style='line-height: 2; font-size: 16px;'>
            <li><strong>Quản lý:</strong> {$count_quanly} tài khoản → admin.php</li>
            <li><strong>Nhân viên:</strong> {$count_nhanvien} tài khoản → admin.php</li>
            <li><strong>Khách hàng:</strong> {$count_khachhang} tài khoản → index.php</li>";
    
    if ($count_other > 0) {
        echo "<li style='color: #856404;'><strong>Vai trò khác:</strong> {$count_other} tài khoản</li>";
    }
    
    echo "</ul>
        <p style='font-size: 16px; color: #155724; margin: 0;'>
            <strong>Tổng:</strong> " . ($count_quanly + $count_nhanvien) . " tài khoản có quyền truy cập admin.php
        </p>
    </div>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}

echo "<div style='text-align: center; margin-top: 30px;'>
    <a href='login.php' style='padding: 12px 24px; background: #088178; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>← Đăng nhập</a>
    <a href='index.php' style='padding: 12px 24px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Trang chủ</a>
</div>

</div>
</body>
</html>";
?>
