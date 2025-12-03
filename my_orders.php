<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
// Ngăn cache để luôn load dữ liệu mới nhất
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php?redirect=my_orders.php');
    exit;
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$orders = array();
$order_details = array();
$success_message = '';
$error_message = '';

// Xử lý gửi yêu cầu đổi trả
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_return'])) {
    $order_id_return = isset($_POST['order_id_return']) ? intval($_POST['order_id_return']) : 0;
    $return_reason = isset($_POST['return_reason']) ? trim($_POST['return_reason']) : '';
    
    if ($order_id_return <= 0) {
        $error_message = 'Đơn hàng không hợp lệ.';
    } elseif (empty($return_reason)) {
        $error_message = 'Vui lòng nhập lý do đổi trả.';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra đơn hàng có thuộc về user này không
            $checkOrderStmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ? AND ma_user = ?");
            $checkOrderStmt->execute(array($order_id_return, $user_id));
            $orderToReturn = $checkOrderStmt->fetch();
            
            if (!$orderToReturn) {
                $error_message = 'Không tìm thấy đơn hàng.';
            } elseif ($orderToReturn['trang_thai'] != 'hoan_thanh') {
                $error_message = 'Chỉ có thể yêu cầu đổi trả đơn hàng đã hoàn thành.';
            } else {
                // Tạo bảng nếu chưa tồn tại
                $conn->exec("CREATE TABLE IF NOT EXISTS don_hang_doi_tra (
                    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    ma_donhang BIGINT(20) NOT NULL,
                    ma_user BIGINT(20) NOT NULL,
                    ly_do TEXT,
                    status ENUM('pending','approved','rejected') DEFAULT 'pending',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_return_order (ma_donhang),
                    KEY idx_return_user (ma_user)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
                
                // Kiểm tra đã tồn tại yêu cầu chưa
                $checkReturnStmt = $conn->prepare("SELECT * FROM don_hang_doi_tra WHERE ma_donhang = ?");
                $checkReturnStmt->execute(array($order_id_return));
                $existingReturn = $checkReturnStmt->fetch();
                
                if ($existingReturn) {
                    // Cập nhật yêu cầu hiện tại
                    $updateReturnStmt = $conn->prepare("UPDATE don_hang_doi_tra SET ly_do = ?, status = 'pending', updated_at = NOW() WHERE ma_donhang = ?");
                    $updateReturnStmt->execute(array($return_reason, $order_id_return));
                    $success_message = 'Yêu cầu đổi trả đã được cập nhật thành công!';
                } else {
                    // Tạo yêu cầu mới
                    $insertReturnStmt = $conn->prepare("INSERT INTO don_hang_doi_tra (ma_donhang, ma_user, ly_do, status) VALUES (?, ?, ?, 'pending')");
                    $insertReturnStmt->execute(array($order_id_return, $user_id, $return_reason));
                    $success_message = 'Yêu cầu đổi trả đã được gửi thành công! Chúng tôi sẽ xử lý trong thời gian sớm nhất.';
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Lỗi khi gửi yêu cầu: ' . $e->getMessage();
        }
    }
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy danh sách đơn hàng của user
    $sql = "SELECT * FROM don_hang WHERE ma_user = ? ORDER BY ma_donhang ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array($user_id));
    $orders = $stmt->fetchAll();

    // Lấy danh sách yêu cầu đổi trả (nếu có)
    $return_requests = array();
    $returnStmt = $conn->prepare("SELECT ma_donhang, status, ly_do, created_at, updated_at FROM don_hang_doi_tra WHERE ma_user = ?");
    $returnStmt->execute(array($user_id));
    $returnRows = $returnStmt->fetchAll();
    foreach ($returnRows as $row) {
        $return_requests[$row['ma_donhang']] = array(
            'status' => $row['status'],
            'ly_do' => $row['ly_do'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        );
    }
    
    // Nếu có yêu cầu xem chi tiết đơn hàng
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $order_id = (int)$_GET['id'];
        
        // Kiểm tra đơn hàng thuộc về user này
        $checkStmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ? AND ma_user = ?");
        $checkStmt->execute(array($order_id, $user_id));
        $selected_order = $checkStmt->fetch();
        
        if ($selected_order) {
            // Lấy chi tiết đơn hàng
            $detailSql = "SELECT 
                            ct.*,
                            sp.ten_sanpham,
                            sp.hinh_anh
                         FROM chitiet_donhang ct
                         INNER JOIN san_pham sp ON ct.id_sanpham = sp.id_sanpham
                         WHERE ct.ma_donhang = ?";
            $detailStmt = $conn->prepare($detailSql);
            $detailStmt->execute(array($order_id));
            $order_details = $detailStmt->fetchAll();
        }
    }
    
} catch (PDOException $e) {
    $error_message = 'Lỗi database: ' . $e->getMessage();
}

// Hàm dịch trạng thái
function translate_status($status) {
    switch ($status) {
        case 'cho_xu_ly': return 'Chờ xử lý';
        case 'xac_nhan': return 'Đã xác nhận';
        case 'da_xuat_kho': return 'Đã xuất kho';
        case 'hoan_thanh': return 'Hoàn thành';
        case 'huy': return 'Đã hủy';
        default: return $status;
    }
}

// Hàm dịch trạng thái thanh toán
function translate_payment_status($status) {
    switch ($status) {
        case 'da_thanh_toan': return 'Đã thanh toán';
        case 'chua_thanh_toan': return 'Chưa thanh toán';
        default: return $status;
    }
}

function translate_return_status($status) {
    switch ($status) {
        case 'pending': return 'Chờ xử lý';
        case 'approved': return 'Đồng ý';
        case 'rejected': return 'Từ chối';
        default: return $status;
    }
}

// Lấy số lượng giỏ hàng
include_once 'includes/cart_count.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>Lịch sử đơn hàng - KLTN Shop</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-id {
            font-size: 18px;
            font-weight: bold;
            color: #088178;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-cho_xu_ly {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-xac_nhan {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-da_xuat_kho {
            background: #d4edda;
            color: #155724;
        }
        
        .status-hoan_thanh {
            background: #d4edda;
            color: #155724;
        }
        
        .status-huy {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-view {
            padding: 8px 20px;
            background: #088178;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-view:hover {
            background: #066d63;
        }
        
        .btn-cancel {
            padding: 8px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-cancel:hover {
            background: #c82333;
        }
        
        .error-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .success-alert {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-alert i,
        .success-alert i {
            margin-right: 8px;
        }

        .btn-return {
            padding: 8px 20px;
            background: #f39c12;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-return:hover {
            background: #d68910;
        }

        .return-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            vertical-align: middle;
        }
        
        .return-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .return-badge.approved {
            background: #d4edda;
            color: #155724;
        }
        
        .return-badge.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Modal đổi trả */
        .return-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            overflow-y: auto;
        }
        
        .return-modal-content {
            background: white;
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            position: relative;
        }
        
        .return-modal h3 {
            color: #088178;
            margin-bottom: 20px;
        }
        
        .return-form-group {
            margin-bottom: 20px;
        }
        
        .return-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .return-form-group textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        
        .return-form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-submit-return {
            padding: 10px 20px;
            background: #088178;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-submit-return:hover {
            background: #066d63;
        }
        
        .btn-cancel-modal {
            padding: 10px 20px;
            background: #ccc;
            color: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-cancel-modal:hover {
            background: #999;
        }
        
        .close-return-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
            line-height: 1;
        }
        
        .close-return-modal:hover {
            color: #333;
        }
        
        .return-info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .return-info-box p {
            margin: 5px 0;
        }
        
        .order-detail-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .order-detail-content {
            background: white;
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 30px;
            cursor: pointer;
            color: #999;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .detail-table th,
        .detail-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .detail-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .detail-table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .empty-orders {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-orders i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-orders p {
            font-size: 18px;
            color: #999;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo1.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#" onclick="toggleSearch(event)"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                        <a href="my_orders.php" class="active">Đơn hàng của tôi</a>
                        <?php 
                        $user_role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : '';
                        if ($user_role === 'quanly'): 
                        ?>
                            <a href="admin.php">Quản trị viên</a>
                        <?php elseif ($user_role === 'nhanvien'): ?>
                            <a href="nhanvienbanhang.php">Quản trị viên</a>
                        <?php elseif ($user_role === 'nhanvienkho'): ?>
                            <a href="nhanvienkho.php">Quản trị viên</a>
                        <?php endif; ?>
                        <a href="logout.php">Đăng xuất</a>
                    </div>
                </li>
                <li id="lg-bag">
                    <a href="cart.php" style="position: relative;">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <a href="#" id="close"><i class="fa-solid fa-xmark"></i></a>    
            </ul> 
            <div id="mobile">
                <a href="cart.php" style="position: relative;">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <i id="bar" class="fas fa-outdent"></i>
            </div>  
        </div>
    </section>

    <section id="page-header" class="about-header">
        <h2>#donhang</h2>
        <p>Lịch sử đơn hàng của bạn</p>
    </section>

    <div class="orders-container section-p1">
        <?php if (!empty($error_message)): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <i class="fa-solid fa-box-open"></i>
                <p>Bạn chưa có đơn hàng nào</p>
                <a href="shop.php" class="continue-shopping-btn">
                    <i class="fa-solid fa-arrow-left"></i> Tiếp tục mua sắm
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">
                                <i class="fas fa-receipt"></i> Đơn hàng #<?php echo $order['ma_donhang']; ?>
                            </div>
                            <div class="order-date">
                                <i class="far fa-calendar"></i> 
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        <div>
                            <span class="order-status status-<?php echo $order['trang_thai']; ?>">
                                <?php echo translate_status($order['trang_thai']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-item">
                            <span class="info-label">Người nhận</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['ten_nguoinhan']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Số điện thoại</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['so_dienthoai']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phương thức thanh toán</span>
                            <span class="info-value">
                                <?php 
                                if ($order['phuongthuc_thanhtoan'] == 'vnpay') {
                                    echo '<i class="fas fa-credit-card"></i> VNPay';
                                } else {
                                    echo '<i class="fas fa-money-bill-wave"></i> ' . htmlspecialchars($order['phuongthuc_thanhtoan']);
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Trạng thái thanh toán</span>
                            <span class="info-value">
                                <?php if ($order['trangthai_thanhtoan'] == 'da_thanh_toan'): ?>
                                    <span style="color: #28a745;">
                                        <i class="fas fa-check-circle"></i> <?php echo translate_payment_status($order['trangthai_thanhtoan']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #dc3545;">
                                        <i class="fas fa-times-circle"></i> <?php echo translate_payment_status($order['trangthai_thanhtoan']); ?>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tổng tiền</span>
                            <span class="info-value" style="color: #088178; font-size: 18px;">
                                <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VNĐ
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="my_orders.php?id=<?php echo $order['ma_donhang']; ?>" class="btn-view">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                        <?php 
                        // Chỉ hiển thị nút hủy cho đơn COD chưa thanh toán và chưa bị hủy
                        if ($order['phuongthuc_thanhtoan'] == 'cod' 
                            && $order['trangthai_thanhtoan'] == 'chua_thanh_toan' 
                            && $order['trang_thai'] != 'huy'
                            && in_array($order['trang_thai'], array('cho_xu_ly', 'xac_nhan'))): 
                        ?>
                        <form method="POST" action="cancel_order.php" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #<?php echo $order['ma_donhang']; ?>?');">
                            <input type="hidden" name="ma_donhang" value="<?php echo $order['ma_donhang']; ?>">
                            <button type="submit" class="btn-cancel">
                                <i class="fas fa-times-circle"></i> Hủy đơn hàng
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php
                        // Cho phép đổi trả với đơn hàng đã hoàn thành hoặc đã xác nhận (cả VNPay và COD)
                        $can_return = in_array($order['trang_thai'], array('hoan_thanh', 'xac_nhan', 'da_xuat_kho'));
                        $return_info = isset($return_requests[$order['ma_donhang']]) ? $return_requests[$order['ma_donhang']] : null;
                        if ($can_return):
                        ?>
                            <?php if (!$return_info): ?>
                                <button type="button" class="btn-return" onclick="openReturnModal(<?php echo $order['ma_donhang']; ?>, '<?php echo htmlspecialchars($order['ten_nguoinhan'], ENT_QUOTES); ?>', '<?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>')">
                                    <i class="fas fa-undo"></i> Yêu cầu đổi trả
                                </button>
                            <?php else: ?>
                                <span class="return-badge <?php echo $return_info['status']; ?>">
                                    <i class="fas fa-info-circle"></i>
                                    <?php echo translate_return_status($return_info['status']); ?>
                                </span>
                                <?php if ($return_info['status'] == 'pending'): ?>
                                    <button type="button" class="btn-return" onclick="openReturnModal(<?php echo $order['ma_donhang']; ?>, '<?php echo htmlspecialchars($order['ten_nguoinhan'], ENT_QUOTES); ?>', '<?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>')">
                                        <i class="fas fa-edit"></i> Cập nhật yêu cầu
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Modal chi tiết đơn hàng -->
    <?php if (isset($_GET['id']) && isset($selected_order) && $selected_order): ?>
    <div class="order-detail-modal" id="orderDetailModal" style="display: block;">
        <div class="order-detail-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            
            <h2 style="margin-bottom: 20px; color: #088178;">
                <i class="fas fa-file-invoice"></i> Chi tiết đơn hàng #<?php echo $selected_order['ma_donhang']; ?>
            </h2>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px;">Thông tin giao hàng</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div>
                        <strong>Người nhận:</strong><br>
                        <?php echo htmlspecialchars($selected_order['ten_nguoinhan']); ?>
                    </div>
                    <div>
                        <strong>Số điện thoại:</strong><br>
                        <?php echo htmlspecialchars($selected_order['so_dienthoai']); ?>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <strong>Địa chỉ:</strong><br>
                        <?php echo htmlspecialchars($selected_order['diachi_nhan']); ?>
                    </div>
                    <div>
                        <strong>Email:</strong><br>
                        <?php echo htmlspecialchars($selected_order['email_nguoinhan']); ?>
                    </div>
                    <div>
                        <strong>Ngày đặt:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($selected_order['created_at'])); ?>
                    </div>
                </div>
            </div>
            
            <h3 style="margin-bottom: 15px;">Sản phẩm đã đặt</h3>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th style="text-align: center;">Số lượng</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($order_details as $index => $detail): 
                        $subtotal = $detail['so_luong'] * $detail['don_gia'];
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($detail['hinh_anh'] ? $detail['hinh_anh'] : 'img/products/f1.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($detail['ten_sanpham']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($detail['ten_sanpham']); ?></td>
                        <td style="text-align: center;"><?php echo $detail['so_luong']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($detail['don_gia'], 0, ',', '.'); ?> VNĐ</td>
                        <td style="text-align: right; font-weight: bold;">
                            <?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"></td>
                        <td style="text-align: right; font-weight: bold;">Tiền hàng:</td>
                        <td style="text-align: right; font-weight: bold;">
                            <?php echo number_format($selected_order['tien_hang'], 0, ',', '.'); ?> VNĐ
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td style="text-align: right; font-weight: bold;">Phí vận chuyển:</td>
                        <td style="text-align: right; font-weight: bold;">
                            <?php echo number_format($selected_order['tien_ship'], 0, ',', '.'); ?> VNĐ
                        </td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td colspan="3"></td>
                        <td style="text-align: right; font-weight: bold; font-size: 18px;">Tổng cộng:</td>
                        <td style="text-align: right; font-weight: bold; font-size: 18px; color: #088178;">
                            <?php echo number_format($selected_order['tong_tien'], 0, ',', '.'); ?> VNĐ
                        </td>
                    </tr>
                </tfoot>
            </table>
            
            <div style="margin-top: 30px; text-align: center;">
                <a href="my_orders.php" class="btn-view">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal đổi trả -->
    <div class="return-modal" id="returnModal">
        <div class="return-modal-content">
            <span class="close-return-modal" onclick="closeReturnModal()">&times;</span>
            <h3><i class="fas fa-undo"></i> Gửi yêu cầu đổi trả</h3>
            
            <div class="return-info-box" id="returnOrderInfo">
                <!-- Thông tin đơn hàng sẽ được điền bằng JavaScript -->
            </div>
            
            <form method="POST" action="my_orders.php" id="returnForm">
                <input type="hidden" name="order_id_return" id="orderIdReturn">
                
                <div class="return-form-group">
                    <label for="returnReason">
                        Lý do đổi trả <span style="color: red;">*</span>
                    </label>
                    <textarea 
                        name="return_reason" 
                        id="returnReason" 
                        placeholder="Vui lòng mô tả chi tiết lý do bạn muốn đổi trả sản phẩm (ví dụ: sản phẩm bị lỗi, không đúng mô tả, giao sai hàng...)"
                        required
                    ></textarea>
                </div>
                
                <div class="return-form-actions">
                    <button type="submit" name="submit_return" class="btn-submit-return">
                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                    </button>
                    <button type="button" class="btn-cancel-modal" onclick="closeReturnModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Search Box -->
    <div class="search-box" id="search-box">
        <div class="search-container">
            <form action="shop.php" method="GET" id="search-form">
                <div class="search-input-wrapper">
                    <input type="text" id="search-input" name="search" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                    <div class="search-suggestions" id="search-suggestions"></div>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        function openReturnModal(orderId, customerName, totalPrice) {
            document.getElementById('returnModal').style.display = 'block';
            document.getElementById('orderIdReturn').value = orderId;
            document.getElementById('returnOrderInfo').innerHTML = 
                '<p><strong>Mã đơn hàng:</strong> #' + orderId + '</p>' +
                '<p><strong>Người nhận:</strong> ' + customerName + '</p>' +
                '<p><strong>Tổng tiền:</strong> ' + totalPrice + ' VNĐ</p>';
            document.getElementById('returnReason').value = '';
            document.body.style.overflow = 'hidden';
        }
        
        function closeReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const returnModal = document.getElementById('returnModal');
            if (event.target === returnModal) {
                closeReturnModal();
            }
        }
        
        function closeModal() {
            window.location.href = 'my_orders.php';
        }
        
        // Đóng modal khi click bên ngoài
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('orderDetailModal');
            if (modal && event.target === modal) {
                closeModal();
            }
        });
    </script>
    
    <!-- Scroll to Top Button -->
    <button id="scrollToTop" title="Trở về đầu trang">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script src="https://cdn.botpress.cloud/webchat/v3.3/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2025/11/26/16/20251126163853-AFN0KSEV.js" defer></script>
</body>
</html>
