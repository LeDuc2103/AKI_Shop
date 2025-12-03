<?php

// Kiểm tra và thiết lập hàm dịch trạng thái
if (!function_exists('translate_status')) {
    function translate_status($status) {
        switch ($status) {
            case 'cho_xu_ly':
                return 'Chờ xử lý';
            case 'xac_nhan':
                return 'Đã xác nhận';
            case 'da_xuat_kho':
                return 'Đã xuất kho';
            case 'hoan_thanh':
                return 'Hoàn thành';
            case 'huy':
                return 'Đã hủy';
            case 'da_thanh_toan':
                return 'Đã thanh toán';
            case 'chua_thanh_toan':
                return 'Chưa thanh toán';
            default:
                return $status;
        }
    }
}

if (!function_exists('translate_payment_status')) {
    function translate_payment_status($status) {
        switch ($status) {
            case 'da_thanh_toan':
                return 'Đã thanh toán';
            case 'chua_thanh_toan':
                return 'Chưa thanh toán';
            default:
                return $status;
        }
    }
}

if (!function_exists('translate_return_status')) {
    function translate_return_status($status) {
        switch ($status) {
            case 'pending':
                return 'Đang chờ xử lý';
            case 'approved':
                return 'Đã chấp nhận';
            case 'rejected':
                return 'Từ chối';
            default:
                return $status;
        }
    }
}

if (!function_exists('admin_fetch_order')) {
    function admin_fetch_order($conn, $orderId) {
        $stmt_order = $conn->prepare("
            SELECT
                d.*,
                d.created_at AS ngay_tao,
                u.ho_ten AS ten_khach_hang_dk,
                u.email AS email_khach_hang_dk,
                u.phone AS sdt_khach_hang_dk
            FROM don_hang d
            LEFT JOIN user u ON d.ma_user = u.ma_user
            WHERE d.ma_donhang = ?
        ");
        $stmt_order->execute(array($orderId));
        return $stmt_order->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('admin_fetch_order_details')) {
    function admin_fetch_order_details($conn, $orderId) {
        $stmt_details = $conn->prepare("
            SELECT
                ct.so_luong,
                ct.don_gia,
                sp.ten_sanpham
            FROM chitiet_donhang ct
            JOIN san_pham sp ON ct.id_sanpham = sp.id_sanpham
            WHERE ct.ma_donhang = ?
        ");
        $stmt_details->execute(array($orderId));
        return $stmt_details->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Đảm bảo $conn đã được thiết lập từ admin.php
if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Lỗi: Không tìm thấy đối tượng kết nối cơ sở dữ liệu \$conn.</div>";
    return;
}

// Đảm bảo bảng đổi trả tồn tại
$conn->exec("CREATE TABLE IF NOT EXISTS don_hang_doi_tra (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    ma_donhang BIGINT(20) NOT NULL,
    ma_user BIGINT(20) NOT NULL,
    ly_do TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    UNIQUE KEY unique_return_order (ma_donhang),
    KEY idx_return_user (ma_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

// *** LẤY ID ĐƠN HÀNG AN TOÀN HƠN và DÙNG intval() ***
$ma_donhang_chi_tiet = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ma_donhang_chi_tiet = intval($_GET['id']);
}

// Xử lý xóa đơn hàng
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    try {
        // Bắt đầu transaction
        $conn->beginTransaction();
        
        // Xóa chi tiết đơn hàng trước (vì có foreign key)
        $stmt_delete_details = $conn->prepare("DELETE FROM chitiet_donhang WHERE ma_donhang = ?");
        $stmt_delete_details->execute(array($delete_id));
        
        // Xóa yêu cầu đổi trả nếu có
        $stmt_delete_return = $conn->prepare("DELETE FROM don_hang_doi_tra WHERE ma_donhang = ?");
        $stmt_delete_return->execute(array($delete_id));
        
        // Xóa đơn hàng
        $stmt_delete_order = $conn->prepare("DELETE FROM don_hang WHERE ma_donhang = ?");
        $stmt_delete_order->execute(array($delete_id));
        
        // Commit transaction
        $conn->commit();
        
        // Redirect về trang danh sách với thông báo thành công
        echo "<script>
            alert('Đã xóa đơn hàng #" . $delete_id . " thành công!');
            window.location.href = 'admin.php?action=donhang';
        </script>";
        exit;
    } catch (PDOException $e) {
        // Rollback nếu có lỗi
        $conn->rollBack();
        echo "<script>
            alert('Lỗi khi xóa đơn hàng: " . addslashes($e->getMessage()) . "');
            window.location.href = 'admin.php?action=donhang';
        </script>";
        exit;
    }
}

// Xuất phiếu đơn hàng
if ($ma_donhang_chi_tiet > 0) {

    $success_message = '';
    $error_message = '';

    $order = admin_fetch_order($conn, $ma_donhang_chi_tiet);
    $return_info = null;
    $returnStmt = $conn->prepare("SELECT * FROM don_hang_doi_tra WHERE ma_donhang = ?");
    $returnStmt->execute(array($ma_donhang_chi_tiet));
    $return_info = $returnStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<div class='alert alert-danger'>Không tìm thấy đơn hàng #" . $ma_donhang_chi_tiet . ".</div>";
        exit;
    }

    $expected_payment_status = ($order['phuongthuc_thanhtoan'] == 'vnpay') ? 'da_thanh_toan' : 'chua_thanh_toan';
    if ($order['trangthai_thanhtoan'] != $expected_payment_status) {
        $payment_text = ($expected_payment_status == 'da_thanh_toan') ? 'đã thanh toán' : 'chưa thanh toán';
        $syncStmt = $conn->prepare("UPDATE don_hang SET trangthai_thanhtoan = ?, thanh_toan = ? WHERE ma_donhang = ?");
        $syncStmt->execute(array($expected_payment_status, $payment_text, $ma_donhang_chi_tiet));
        if ($success_message !== '') {
            $success_message .= ' ';
        }
        $success_message .= 'Trạng thái thanh toán đã được đồng bộ tự động.';
        $order = admin_fetch_order($conn, $ma_donhang_chi_tiet);
    }

    $order_details = admin_fetch_order_details($conn, $ma_donhang_chi_tiet);

    // HIỂN THỊ PHIẾU CHI TIẾT
    ?>
    <div class="container mt-4">
        <div class="card shadow-lg p-4 mb-5">
            <div class="card-body">
                <h2 class="text-center mb-4 text-primary"><i class="fas fa-file-invoice me-2"></i> PHIẾU ĐƠN HÀNG #<?php echo htmlspecialchars($order['ma_donhang']); ?></h2>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <hr>

                <div class="row mb-4 border p-3 rounded bg-light">
                    <div class="col-md-6 border-end">
                        <h5 class="text-secondary"><i class="fas fa-info-circle me-2"></i> Thông tin chung Đơn hàng</h5>
                        <p><strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></p>
                        <p><strong>Trạng thái Đơn hàng:</strong> <span class="badge bg-info"><?php echo translate_status($order['trang_thai']); ?></span></p>
                        <p><strong>Phương thức TT:</strong> <?php echo htmlspecialchars($order['phuongthuc_thanhtoan']); ?></p>
                        <p><strong>Trạng thái TT:</strong>
                            <span class="badge bg-<?php echo ($order['trangthai_thanhtoan'] == 'da_thanh_toan' ? 'success' : 'warning text-dark'); ?>">
                                <?php echo translate_payment_status($order['trangthai_thanhtoan']); ?>
                            </span>
                        </p>
                        <small class="text-muted d-block">Hệ thống tự động đặt trạng thái thanh toán dựa trên phương thức: VNPay = đã thanh toán, COD = chưa thanh toán.</small>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-secondary"><i class="fas fa-user-tag me-2"></i> Thông tin Người nhận</h5>
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['ten_nguoinhan']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['sdt_khach_hang_dk'] ? $order['sdt_khach_hang_dk'] : 'Không có'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email_nguoinhan']); ?></p>
                        <p><strong>Địa chỉ nhận:</strong> <?php echo htmlspecialchars($order['diachi_nhan']); ?></p>
                        <!-- PHP < 5.3 fix: Sử dụng isset() và toán tử ternary truyền thống -->
                        <p><strong>Khách hàng ĐK:</strong> <?php echo htmlspecialchars((isset($order['ten_khach_hang_dk']) && !empty($order['ten_khach_hang_dk'])) ? $order['ten_khach_hang_dk'] : 'Khách vãng lai'); ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h5 class="text-secondary"><i class="fas fa-info-circle me-2"></i> Trạng thái đơn hàng</h5>
                            <div class="mt-3">
                                <p class="mb-2"><strong>Trạng thái hiện tại:</strong></p>
                                <h4>
                                    <?php
                                    $status_badge_class = 'bg-secondary';
                                    if ($order['trang_thai'] == 'hoan_thanh') {
                                        $status_badge_class = 'bg-success';
                                    } elseif ($order['trang_thai'] == 'xac_nhan') {
                                        $status_badge_class = 'bg-info';
                                    } elseif ($order['trang_thai'] == 'cho_xu_ly') {
                                        $status_badge_class = 'bg-warning text-dark';
                                    } elseif ($order['trang_thai'] == 'huy') {
                                        $status_badge_class = 'bg-danger';
                                    } elseif ($order['trang_thai'] == 'da_xuat_kho') {
                                        $status_badge_class = 'bg-primary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_badge_class; ?> fs-5">
                                        <?php echo translate_status($order['trang_thai']); ?>
                                    </span>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light">
                            <h5 class="text-secondary"><i class="fas fa-money-check-alt me-2"></i> Trạng thái thanh toán</h5>
                            <p class="mb-1"><strong>Phương thức:</strong> <?php echo htmlspecialchars($order['phuongthuc_thanhtoan']); ?></p>
                            <p class="mb-1"><strong>Trạng thái hiện tại:</strong>
                                <span class="badge bg-<?php echo ($order['trangthai_thanhtoan'] == 'da_thanh_toan' ? 'success' : 'warning text-dark'); ?>">
                                    <?php echo translate_payment_status($order['trangthai_thanhtoan']); ?>
                                </span>
                            </p>
                            <small class="text-muted d-block">Hệ thống tự động đặt trạng thái này dựa trên phương thức thanh toán: VNPay = Đã thanh toán, COD = Chưa thanh toán.</small>
                        </div>
                    </div>
                </div>

                <?php if ($return_info): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="border rounded p-3 bg-white">
                            <h5 class="text-secondary"><i class="fas fa-undo me-2"></i> Yêu cầu đổi trả</h5>
                            <p><strong>Trạng thái:</strong> <?php echo translate_return_status($return_info['status']); ?></p>
                            <p><strong>Lý do:</strong> <?php echo nl2br(htmlspecialchars($return_info['ly_do'])); ?></p>
                            <p><strong>Ngày gửi:</strong> <?php echo date('d/m/Y H:i', strtotime($return_info['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- BẢNG CHI TIẾT SẢN PHẨM -->
                <h4 class="mt-4 mb-3"><i class="fas fa-box-open me-2"></i> Chi tiết Sản phẩm</h4>
                <table class="table table-bordered table-striped">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Tên sản phẩm</th>
                            <th class="text-end" style="width: 15%;">Đơn giá</th>
                            <th class="text-center" style="width: 10%;">SL</th>
                            <th class="text-end" style="width: 15%;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        if (!empty($order_details)):
                            foreach ($order_details as $item):
                                // PHP < 5.3 fix: Sử dụng isset() và toán tử ternary truyền thống
                                $ten_sanpham = (isset($item['ten_sanpham']) && $item['ten_sanpham'] !== NULL) ? $item['ten_sanpham'] : 'Không rõ tên (Lỗi Join)';
                                $so_luong = (isset($item['so_luong']) && $item['so_luong'] !== NULL) ? $item['so_luong'] : 0;
                                $don_gia = (isset($item['don_gia']) && $item['don_gia'] !== NULL) ? $item['don_gia'] : 0;
                                $subtotal = $so_luong * $don_gia;
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($ten_sanpham); ?></td>
                                <td class="text-end"><?php echo number_format($don_gia, 0, ',', '.'); ?>₫</td>
                                <td class="text-center"><?php echo $so_luong; ?></td>
                                <td class="text-end"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</td>
                            </tr>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center text-danger">
                                    Không tìm thấy chi tiết sản phẩm. (Kiểm tra dữ liệu trong bảng `chitiet_donhang`).
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Bảng Tổng hợp Thanh toán -->
                <h4 class="mt-4 mb-3"><i class="fas fa-calculator me-2"></i> Chi tiết Thanh toán</h4>
                <table class="table table-bordered table-sm">
                    <tbody class="table-light">
                        <tr>
                            <th class="text-end" style="width: 75%;">Tiền hàng (Giá trị hàng hóa)</th>
                            <td class="text-end"><?php echo number_format($order['tien_hang'], 0, ',', '.'); ?>₫</td>
                        </tr>
                        <tr>
                            <th class="text-end">Phí vận chuyển</th>
                            <td class="text-end"><?php echo number_format($order['tien_ship'], 0, ',', '.'); ?>₫</td>
                        </tr>
                        <?php
                        // Tính toán Giảm giá
                        $giam_gia = $order['tien_hang'] + $order['tien_ship'] - $order['tong_tien'];
                        if ($giam_gia > 0):
                        ?>
                        <tr>
                            <th class="text-end">Giảm giá/Voucher</th>
                            <td class="text-end text-danger">- <?php echo number_format($giam_gia, 0, ',', '.'); ?>₫</td>
                        </tr>
                        <?php endif; ?>
                        <tr class="table-success">
                            <th colspan="1" class="text-end h5">TỔNG CỘNG THANH TOÁN</th>
                            <td class="text-end h5"><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>₫</td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-center mt-5">
                    <a href="admin.php?action=donhang" class="btn btn-secondary me-3"><i class="fas fa-arrow-left"></i> Quay lại Danh sách</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> In phiếu</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* CSS cho chế độ In */
        @media print {
            .sidebar, .me-3, .btn-secondary, .btn-primary {
                display: none !important;
            }
            .container {
                width: 100% !important;
                padding: 0 !important;
            }
            .card {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
    <?php
    // Bắt buộc dừng chương trình tại đây
    exit;

}
// Danh sách đơn hàng
else {
    // Nếu không có ID hợp lệ, hiển thị danh sách

    $search_term = '';
    $search_query = '';
    // PHP < 5.3 fix: Khởi tạo mảng bằng array()
    $params = array();

    // 1. Xử lý tìm kiếm
    if (isset($_GET['search_term']) && trim($_GET['search_term']) !== '') {
        $search_term = trim($_GET['search_term']);

        // LOGIC TÌM KIẾM: Tách tìm ID chính xác và tìm chuỗi tương đối
        if (is_numeric($search_term) && intval($search_term) > 0) {
            // Nếu là số, chỉ tìm kiếm chính xác theo ID đơn hàng
            $search_query = " AND d.ma_donhang = ? ";
            $params[] = intval($search_term);
        } else {
            // Nếu là chuỗi, tìm kiếm tương đối theo Tên hoặc Email
            $search_query = " AND (d.ten_nguoinhan LIKE ? OR d.email_nguoinhan LIKE ?) ";
            $params[] = '%' . $search_term . '%';
            $params[] = '%' . $search_term . '%';
        }
    }

    // 2. PHÂN TRANG
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // 3. ĐẾM TỔNG SỐ ĐƠN HÀNG (để tính số trang)
    $sql_count = "
        SELECT COUNT(*) as total
        FROM don_hang d
        LEFT JOIN user u ON d.ma_user = u.ma_user
        WHERE 1=1 " . $search_query;
    
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute($params);
    $total_orders = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $total_orders = $total_orders['total'];
    $total_pages = ceil($total_orders / $per_page);

    // 4. LẤY DANH SÁCH ĐƠN HÀNG VỚI PHÂN TRANG
    $sql = "
        SELECT
            d.ma_donhang,
            d.created_at AS ngay_tao,
            d.tong_tien,
            d.trang_thai,
            d.trangthai_thanhtoan,
            d.phuongthuc_thanhtoan,
            d.ten_nguoinhan,
            d.email_nguoinhan,
            u.ho_ten AS ten_khach_hang,
            u.phone AS sdt_khachhang
        FROM don_hang d
        LEFT JOIN user u ON d.ma_user = u.ma_user
        WHERE 1=1 " . $search_query . "
        ORDER BY d.ma_donhang ASC
        LIMIT " . intval($per_page) . " OFFSET " . intval($offset);

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // HIỂN THỊ DANH SÁCH
    ?>
    <div class="container mt-4">
        <h2><i class="fas fa-receipt me-2"></i> Quản lý Đơn hàng</h2>

        <!-- FORM TÌM KIẾM -->
        <div class="d-flex justify-content-end align-items-center mb-3">
            <form method="GET" action="admin.php" class="d-flex">
                <input type="hidden" name="action" value="donhang">

                <input type="text" name="search_term" class="form-control me-2" placeholder="ID (chính xác) hoặc Tên/Email (tương đối)" value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($search_term)): ?>
                    <a href="admin.php?action=donhang" class="btn btn-outline-danger ms-2" title="Xóa tìm kiếm"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($search_term)): ?>
            <p class="text-info">Hiển thị kết quả tìm kiếm cho: **<?php echo htmlspecialchars($search_term); ?>**</p>
        <?php endif; ?>

        <table class="table table-striped table-hover mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Người nhận</th>
                    <th>Email nhận</th>
                    <th>Số điện thoại</th>
                    <th>Ngày tạo</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái đơn hàng</th>
                    <th>Trạng thái thanh toán</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="9" class="text-center">Không tìm thấy đơn hàng nào.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $row): ?>
                    <tr>
                        <td><?php echo $row['ma_donhang']; ?></td>
                        <td><?php echo htmlspecialchars($row['ten_nguoinhan']); ?></td>
                        <td><?php echo htmlspecialchars($row['email_nguoinhan']); ?></td>
                        <td><?php echo htmlspecialchars($row['sdt_khachhang'] ? $row['sdt_khachhang'] : '-'); ?></td>
                        <td><?php echo $row['ngay_tao']; ?></td>
                        <td><?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>₫</td>
                        <td>
                            <?php
                            $status_text = translate_status($row['trang_thai']);
                            $status_class = 'bg-secondary';
                            if ($row['trang_thai'] == 'hoan_thanh'):
                                $status_class = 'bg-success';
                            elseif ($row['trang_thai'] == 'cho_xu_ly' || $row['trang_thai'] == 'xac_nhan'):
                                $status_class = 'bg-warning text-dark';
                            elseif ($row['trang_thai'] == 'huy'):
                                $status_class = 'bg-danger';
                            endif;
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td>
                            <?php
                            $payment_status = isset($row['trangthai_thanhtoan']) ? $row['trangthai_thanhtoan'] : 'chua_thanh_toan';
                            $payment_class = ($payment_status == 'da_thanh_toan') ? 'bg-success' : 'bg-warning text-dark';
                            ?>
                            <span class="badge <?php echo $payment_class; ?>">
                                <?php echo translate_payment_status($payment_status); ?>
                            </span>
                            <div class="small text-muted"><?php echo htmlspecialchars($row['phuongthuc_thanhtoan']); ?></div>
                        </td>
                        <td>
                            <!-- Nút Xem chi tiết trỏ đến chính file này với tham số ID -->
                            <a href="admin.php?action=donhang&id=<?php echo $row['ma_donhang']; ?>" class="btn btn-sm btn-primary" title="Xem phiếu đơn hàng">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                            <a href="admin.php?action=donhang&delete_id=<?php echo $row['ma_donhang']; ?>" 
                               class="btn btn-sm btn-danger" 
                               title="Xóa đơn hàng"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng #<?php echo $row['ma_donhang']; ?>? Hành động này không thể hoàn tác!');">
                                <i class="fas fa-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- PHÂN TRANG -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Phân trang đơn hàng">
            <ul class="pagination justify-content-center">
                <?php
                $query_base = 'admin.php?action=donhang';
                if ($search_term != '') $query_base .= '&search_term=' . urlencode($search_term);
                
                $prev = $page - 1;
                $next = $page + 1;
                
                if ($page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=1">&laquo;</a></li>';
                    echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $prev . '">&lsaquo;</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
                    echo '<li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>';
                }
                
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++) {
                    if ($i == $page) {
                        echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                    } else {
                        echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $i . '">' . $i . '</a></li>';
                    }
                }
                
                if ($page < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $next . '">&rsaquo;</a></li>';
                    echo '<li class="page-item"><a class="page-link" href="' . $query_base . '&page=' . $total_pages . '">&raquo;</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>';
                    echo '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
                }
                ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <?php
}
?>