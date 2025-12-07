<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>🔍 Kiểm tra vai_tro trong database</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #088178; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #088178; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .highlight { background: #fffacd; font-weight: bold; }
        .badge { padding: 5px 10px; border-radius: 3px; color: white; font-weight: bold; }
        .badge-admin { background: #dc3545; }
        .badge-quanly { background: #28a745; }
        .badge-user { background: #6c757d; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Kiểm tra giá trị vai_tro trong bảng user</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT ma_user, ho_ten, email, vai_tro, trang_thai FROM user ORDER BY ma_user");
    
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Vai trò (CHÍNH XÁC)</th>
            <th>Trạng thái</th>
            <th>Phân loại</th>
          </tr>";
    
    $count = 0;
    $vai_tro_list = array();
    
    while ($row = $stmt->fetch()) {
        $count++;
        $vai_tro = $row['vai_tro'];
        $vai_tro_lower = strtolower(trim($vai_tro));
        
        // Thống kê vai_tro
        if (!isset($vai_tro_list[$vai_tro])) {
            $vai_tro_list[$vai_tro] = 0;
        }
        $vai_tro_list[$vai_tro]++;
        
        // Phân loại
        $phan_loai = '';
        $badge_class = 'badge-user';
        if ($vai_tro_lower === 'quan ly' || $vai_tro_lower === 'quanly') {
            $phan_loai = '→ ADMIN.PHP ✅';
            $badge_class = 'badge-quanly';
        } elseif ($vai_tro_lower === 'nhanvien') {
            $phan_loai = '→ nhanvien.PHP ✅';
            $badge_class = 'badge-admin';
        } else {
            $phan_loai = '→ INDEX.PHP';
        }
        
        $is_highlight = ($vai_tro_lower === 'quanly' || $vai_tro_lower === 'nhanvien' || $vai_tro_lower === 'khachhang');
        
        echo "<tr" . ($is_highlight ? " class='highlight'" : "") . ">";
        echo "<td><strong>" . $row['ma_user'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['ho_ten']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['email']) . "</strong></td>";
        echo "<td><span class='badge " . $badge_class . "'>" . htmlspecialchars($vai_tro) . "</span> <small style='color: #666;'>('" . htmlspecialchars($vai_tro) . "')</small></td>";
        echo "<td>" . $row['trang_thai'] . "</td>";
        echo "<td><strong>" . $phan_loai . "</strong></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>📊 Thống kê vai_tro:</h3>";
    echo "<ul style='line-height: 2;'>";
    foreach ($vai_tro_list as $vt => $count) {
        echo "<li><strong>'" . htmlspecialchars($vt) . "'</strong> → " . $count . " tài khoản</li>";
    }
    echo "</ul>";
    
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0;'>
        <h4 style='color: #0c5460;'>💡 Kết luận:</h4>
        <p style='font-size: 16px;'>Trong database, vai_tro có các giá trị: <strong>" . implode(", ", array_map('htmlspecialchars', array_keys($vai_tro_list))) . "</strong></p>
        <p style='font-size: 16px;'>Cần sửa logic login.php để kiểm tra: <code>vai_tro = 'Quan ly'</code> hoặc <code>= 'admin'</code></p>
    </div>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}

echo "<div style='text-align: center; margin-top: 30px;'>
    <a href='login.php' style='padding: 12px 24px; background: #088178; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>← Về trang đăng nhập</a>
    <a href='fix_admin_role.php' style='padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>Sửa vai trò</a>
</div>

</div>
</body>
</html>";
?>
