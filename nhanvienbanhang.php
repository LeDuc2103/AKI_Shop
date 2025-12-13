<?php
session_start();
require_once 'config/database.php';

// Kiểm tra quyền nhân viên
if (!isset($_SESSION['nhanvien_logged_in']) || $_SESSION['nhanvien_logged_in'] != true) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Lấy thông tin nhân viên
$nhanvienInfo = null;
if (isset($_SESSION['nhanvien_email'])) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND LOWER(vai_tro) = 'nhanvien'");
    $stmt->execute(array($_SESSION['nhanvien_email']));
    $nhanvienInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Xác định action
$action = isset($_GET['action']) ? $_GET['action'] : 'donhang';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Nhân Viên Bán Hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css?v=1765636815">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px 15px; }
        .sidebar a:hover { background-color: #495057; }
        .sidebar a.active { background-color: #495057; font-weight: bold; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-2 p-0 sidebar">
            <?php
            // Hiển thị thông tin nhân viên
            if (isset($nhanvienInfo)) {
            ?>
                <div class="text-center text-white p-3 border-bottom">
                    <i class="fas fa-user-tie fa-2x mb-2"></i>
                    <h5><?php echo htmlspecialchars($nhanvienInfo['ho_ten']); ?></h5>
                    <small><?php echo htmlspecialchars($nhanvienInfo['vai_tro']); ?></small>
                </div>
            <?php
            }
            ?>
            <a href="index.php"><i class="fas fa-home me-2"></i> Trang chủ</a>
            <a href="nhanvienbanhang.php?action=donhang" class="<?php echo ($action == 'donhang') ? 'active' : ''; ?>">
                <i class="fas fa-receipt me-2"></i> Cập nhật đơn hàng
            </a>
            <a href="nhanvienbanhang.php?action=doi_tra" class="<?php echo ($action == 'doi_tra') ? 'active' : ''; ?>">
                <i class="fas fa-undo-alt me-2"></i> Quản lý đổi trả
            </a>
            <a href="nhanvienbanhang.php?action=danh_gia" class="<?php echo ($action == 'danh_gia') ? 'active' : ''; ?>">
                <i class="fas fa-star me-2"></i> Quản lý Đánh giá
            </a>
            <a href="nhanvienbanhang.php?action=tin_tuc" class="<?php echo ($action == 'tin_tuc') ? 'active' : ''; ?>">
                <i class="fas fa-newspaper me-2"></i> Quản lý tin tức
            </a>
            <a href="nhanvienbanhang.php?action=banner" class="<?php echo ($action == 'banner') ? 'active' : ''; ?>">
                <i class="fas fa-images me-2"></i> Quản lý Banner
            </a>
            <a href="nhanvienbanhang.php?action=hotro" class="<?php echo ($action == 'hotro') ? 'active' : ''; ?>">
                <i class="fas fa-headset me-2"></i> Hỗ Trợ
            </a>
            <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
        </div>

        <!-- Nội dung chính -->
        <div class="col-md-10 p-4">
            <?php
            // Lưu ID nhân viên vào session để sử dụng trong các panel
            if (isset($nhanvienInfo['ma_user'])) {
                $_SESSION['nhanvien_id'] = $nhanvienInfo['ma_user'];
            }
            
            if ($action == 'donhang') {
                include('nhanvien/donhang.php');
            } elseif ($action == 'doi_tra') {
                include('nhanvien/doi_tra.php');
            } elseif ($action == 'danh_gia') {
                include('nhanvien/danh_gia.php');
            } elseif ($action == 'tin_tuc') {
                include('nhanvien/tin_tuc.php');
            } elseif ($action == 'banner') {
                include('nhanvien/banner.php');
            } elseif ($action == 'hotro') {
                include('nhanvien/hotro.php');
            } else {
                include('nhanvien/donhang.php');
            }
            ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/mobile-responsive.js?v=1765636815"></script>
</body>
</html>
