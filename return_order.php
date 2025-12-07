<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php?redirect=return_order.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];
$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
$error_message = '';
$success_message = '';
$order = null;

// Tạo bảng don_hang_doi_tra nếu chưa có
$conn->exec("CREATE TABLE IF NOT EXISTS don_hang_doi_tra (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ma_donhang BIGINT(20) NOT NULL,
    ma_user BIGINT(20) NOT NULL,
    ly_do TEXT,
    bang_chung VARCHAR(250) DEFAULT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    UNIQUE KEY unique_return_order (ma_donhang),
    KEY idx_return_user (ma_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

if ($order_id <= 0) {
    $error_message = 'Đơn hàng không hợp lệ.';
} else {
    $stmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ? AND ma_user = ?");
    $stmt->execute(array($order_id, $user_id));
    $order = $stmt->fetch();
    if (!$order) {
        $error_message = 'Không tìm thấy đơn hàng.';
    } elseif ($order['trang_thai'] != 'hoan_thanh') {
        $error_message = 'Chỉ có thể yêu cầu đổi trả khi đơn hàng ở trạng thái hoàn thành.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_message)) {
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    if ($reason == '') {
        $error_message = 'Vui lòng nhập lý do đổi trả.';
    } else {
        // Xử lý upload bằng chứng (hình ảnh hoặc video)
        $bang_chung = '';
        if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] == 0) {
            $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/mov');
            $max_size = 50 * 1024 * 1024; // 50MB
            
            $file_type = $_FILES['evidence']['type'];
            $file_size = $_FILES['evidence']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF) hoặc video (MP4, AVI, MOV).';
            } elseif ($file_size > $max_size) {
                $error_message = 'Kích thước file không được vượt quá 50MB.';
            } else {
                // Tạo thư mục nếu chưa có
                $upload_dir = 'img/evidence/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Tạo tên file unique
                $file_extension = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
                $new_filename = 'evidence_' . $order_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['evidence']['tmp_name'], $upload_path)) {
                    $bang_chung = $upload_path;
                } else {
                    $error_message = 'Lỗi khi upload file. Vui lòng thử lại.';
                }
            }
        }
        
        if (empty($error_message)) {
            $checkStmt = $conn->prepare("SELECT * FROM don_hang_doi_tra WHERE ma_donhang = ?");
            $checkStmt->execute(array($order_id));
            $existing = $checkStmt->fetch();
            if ($existing) {
                if ($bang_chung) {
                    $updateStmt = $conn->prepare("UPDATE don_hang_doi_tra SET ly_do = ?, bang_chung = ?, status = 'pending', updated_at = NOW() WHERE ma_donhang = ?");
                    $updateStmt->execute(array($reason, $bang_chung, $order_id));
                } else {
                    $updateStmt = $conn->prepare("UPDATE don_hang_doi_tra SET ly_do = ?, status = 'pending', updated_at = NOW() WHERE ma_donhang = ?");
                    $updateStmt->execute(array($reason, $order_id));
                }
            } else {
                if ($bang_chung) {
                    $insertStmt = $conn->prepare("INSERT INTO don_hang_doi_tra (ma_donhang, ma_user, ly_do, bang_chung, status, updated_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
                    $insertStmt->execute(array($order_id, $user_id, $reason, $bang_chung));
                } else {
                    $insertStmt = $conn->prepare("INSERT INTO don_hang_doi_tra (ma_donhang, ma_user, ly_do, status, updated_at) VALUES (?, ?, ?, 'pending', NOW())");
                    $insertStmt->execute(array($order_id, $user_id, $reason));
                }
            }
            $success_message = 'Yêu cầu đổi trả đã được gửi. Chúng tôi sẽ liên hệ bạn sớm.';
        }
    }
}

include_once 'includes/cart_count.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu đổi trả - KLTN Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .return-container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .return-container h2 {
            color: #088178;
            margin-bottom: 20px;
        }
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            font-size: 14px;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn-primary {
            background: #088178;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-secondary {
            background: #ccc;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo7.png" width="150px" class="logo" alt="KLTN Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Về chúng tôi</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
                <li id="search-icon"><a href="#"><i class="fa-solid fa-search"></i></a></li>
                <li id="user-icon" tabindex="0">
                    <a href="#" tabindex="-1"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <a href="#">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                        <a href="my_orders.php">Đơn hàng của tôi</a>
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

    <div class="return-container">
        <h2><i class="fas fa-undo"></i> Yêu cầu đổi trả</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($order && empty($success_message)): ?>
            <div class="order-summary">
                <p><strong>Mã đơn hàng:</strong> #<?php echo $order['ma_donhang']; ?></p>
                <p><strong>Ngày hoàn thành:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Tổng tiền:</strong> <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VNĐ</p>
            </div>

            <form method="post" action="return_order.php" enctype="multipart/form-data">
                <input type="hidden" name="order_id" value="<?php echo $order['ma_donhang']; ?>">
                <label for="reason"><strong>Lý do đổi trả</strong></label>
                <textarea name="reason" id="reason" placeholder="Mô tả chi tiết vấn đề bạn gặp phải (sai sản phẩm, lỗi, thiếu phụ kiện...)" required></textarea>
                
                <div style="margin-top: 15px;">
                    <label for="evidence"><strong>Bằng chứng (Hình ảnh hoặc Video)</strong></label>
                    <p style="font-size: 13px; color: #666; margin: 5px 0;">
                        <i class="fas fa-info-circle"></i> Upload hình ảnh hoặc video chứng minh vấn đề (tối đa 50MB)
                    </p>
                    <input type="file" name="evidence" id="evidence" accept="image/jpeg,image/png,image/gif,video/mp4,video/avi,video/mov" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
                    <small style="color: #888;">Hỗ trợ: JPG, PNG, GIF, MP4, AVI, MOV</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Gửi yêu cầu</button>
                    <a href="my_orders.php" class="btn-secondary" style="text-decoration: none;"><i class="fas fa-arrow-left"></i> Quay lại đơn hàng</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>

