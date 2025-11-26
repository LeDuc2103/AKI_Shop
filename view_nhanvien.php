<?php
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <title>Dữ liệu bảng user</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #088178; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #088178; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .badge { padding: 5px 10px; border-radius: 3px; color: white; font-weight: bold; }
        .badge-admin { background: #dc3545; }
        .badge-user { background: #6c757d; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📊 Dữ liệu bảng user</h1>";

try {
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT * FROM user where vai_tro == 'quanly' vai_tro == 'nhanvien' ORDER BY ma_user");
    
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Password (20 ký tự)</th>
            <th>Phone</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
          </tr>";
    
    $count = 0;
    while ($row = $stmt->fetch()) {
        $count++;
        echo "<tr>";
        echo "<td><strong>" . $row['ma_user'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['ho_ten']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['email']) . "</strong></td>";
        echo "<td style='font-family: monospace; font-size: 11px;'>" . substr($row['password'], 0, 20) . "...</td>";
        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td><span class='badge badge-" . ($row['vai_tro'] == 'admin' ? 'admin' : 'user') . "'>" . htmlspecialchars($row['vai_tro']) . "</span></td>";
        echo "<td>" . $row['trang_thai'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<p><strong>Tổng số: {$count} tài khoản</strong></p>";
    
    echo "<h3>🔐 Test password MD5:</h3>";
    echo "<p>MD5('123456') = <strong>" . md5('123456') . "</strong></p>";
    echo "<p>MD5('admin') = <strong>" . md5('admin') . "</strong></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 30px;'>
    <a href='login.php' style='padding: 12px 24px; background: #088178; color: white; text-decoration: none; border-radius: 5px;'>← Về trang đăng nhập</a>
</div>

</div>
</body>
</html>";
?>
