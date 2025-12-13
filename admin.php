<?php
session_start();
require_once 'config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header('Location: login.php');
    exit();
}

// Khởi tạo database connection
$db = new Database();
$conn = $db->getConnection();

// Lấy thông tin admin
$adminInfo = null;
if (isset($_SESSION['admin_email'])) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND LOWER(vai_tro) IN ('quanly','nhanvien','nhanvienkho')");
    $stmt->execute(array($_SESSION['admin_email']));
    $adminInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Xác định action
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Quản Trị</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css?v=1765636815">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px 15px; }
        .sidebar a:hover { background-color: #495057; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-2 p-0 sidebar">
            <?php include('admin/include/sidebar.php'); ?>
        </div>

        <!-- Nội dung chính -->
        <div class="col-md-10 p-4">
            <?php
            if ($action == 'dashboard') {
                include('admin/dashboard.php');
            } elseif ($action == 'sanpham') {
                include('admin/sanpham.php');
            } elseif ($action == 'nhanvien') {
                include('admin/nhanvien.php');
            } elseif ($action == 'khachhang') {
                include('admin/khachhang.php');
            } elseif ($action == 'donhang') {
                include('admin/donhang.php');
            } elseif ($action == 'danhmuc') {
                include('admin/danhmuc.php');
            } elseif ($action == 'khuyenmai') {
                include('admin/khuyenmai.php');
            } elseif ($action == 'hoan_tien') {
                include('admin/hoan_tien.php');
            } else {
                include('admin/dashboard.php');
            }
            
            ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/mobile-responsive.js?v=1765636815"></script>
</body>
</html>
