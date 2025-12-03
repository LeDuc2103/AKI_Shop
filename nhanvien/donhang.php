<?php
// File: nhanvien/donhang.php
// Chức năng: Cập nhật trạng thái đơn hàng và trạng thái thanh toán cho nhân viên bán hàng

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

if (!function_exists('nhanvien_fetch_order')) {
    function nhanvien_fetch_order($conn, $orderId) {
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

if (!function_exists('nhanvien_fetch_order_details')) {
    function nhanvien_fetch_order_details($conn, $orderId) {
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

// Đảm bảo $conn đã được thiết lập
if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Lỗi: Không tìm thấy đối tượng kết nối cơ sở dữ liệu \$conn.</div>";
    return;
}

// Lấy ID đơn hàng
$ma_donhang_chi_tiet = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ma_donhang_chi_tiet = intval($_GET['id']);
}

// Xem chi tiết đơn hàng
if ($ma_donhang_chi_tiet > 0) {

    $order_status_options = array('cho_xu_ly', 'xac_nhan', 'da_xuat_kho', 'hoan_thanh', 'huy');
    $payment_status_options = array('chua_thanh_toan', 'da_thanh_toan');
    $success_message = '';
    $error_message = '';

    // Xử lý cập nhật trạng thái
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Cập nhật trạng thái đơn hàng
        if (isset($_POST['update_order_status'])) {
            $new_status = isset($_POST['trang_thai']) ? trim($_POST['trang_thai']) : '';
            if (in_array($new_status, $order_status_options)) {
                $updateStmt = $conn->prepare("UPDATE don_hang SET trang_thai = ? WHERE ma_donhang = ?");
                $updateStmt->execute(array($new_status, $ma_donhang_chi_tiet));
                $success_message = 'Cập nhật trạng thái đơn hàng thành công.';
            } else {
                $error_message = 'Trạng thái đơn hàng không hợp lệ.';
            }
        }

        // Cập nhật trạng thái thanh toán
        if (isset($_POST['update_payment_status'])) {
            $new_payment_status = isset($_POST['trangthai_thanhtoan']) ? trim($_POST['trangthai_thanhtoan']) : '';
            if (in_array($new_payment_status, $payment_status_options)) {
                $payment_text = ($new_payment_status == 'da_thanh_toan') ? 'đã thanh toán' : 'chưa thanh toán';
                $updatePaymentStmt = $conn->prepare("UPDATE don_hang SET trangthai_thanhtoan = ?, thanh_toan = ? WHERE ma_donhang = ?");
                $updatePaymentStmt->execute(array($new_payment_status, $payment_text, $ma_donhang_chi_tiet));
                $success_message = 'Cập nhật trạng thái thanh toán thành công.';
            } else {
                $error_message = 'Trạng thái thanh toán không hợp lệ.';
            }
        }
    }

    // Lấy thông tin đơn hàng
    $order = nhanvien_fetch_order($conn, $ma_donhang_chi_tiet);

    if (!$order) {
        echo "<div class='alert alert-danger'>Không tìm thấy đơn hàng #" . $ma_donhang_chi_tiet . ".</div>";
        exit;
    }

    $order_details = nhanvien_fetch_order_details($conn, $ma_donhang_chi_tiet);

    // HIỂN THỊ PHIẾU CHI TIẾT
    ?>
    <div class="container-fluid">
        <div class="card shadow-lg p-4 mb-5">
            <div class="card-body">
                <h2 class="text-center mb-4 text-primary">
                    <i class="fas fa-file-invoice me-2"></i> CHI TIẾT ĐƠN HÀNG #<?php echo htmlspecialchars($order['ma_donhang']); ?>
                </h2>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <hr>

                <!-- Thông tin đơn hàng -->
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
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-secondary"><i class="fas fa-user-tag me-2"></i> Thông tin Người nhận</h5>
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['ten_nguoinhan']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars(isset($order['so_dienthoai']) ? $order['so_dienthoai'] : 'Không có'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email_nguoinhan']); ?></p>
                        <p><strong>Địa chỉ nhận:</strong> <?php echo htmlspecialchars($order['diachi_nhan']); ?></p>
                        <p><strong>Khách hàng ĐK:</strong> <?php echo htmlspecialchars((isset($order['ten_khach_hang_dk']) && !empty($order['ten_khach_hang_dk'])) ? $order['ten_khach_hang_dk'] : 'Khách vãng lai'); ?></p>
                    </div>
                </div>

                <!-- Form cập nhật trạng thái -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h5 class="text-secondary"><i class="fas fa-random me-2"></i> Cập nhật trạng thái đơn hàng</h5>
                            <form method="post" action="nhanvienbanhang.php?action=donhang&id=<?php echo $order['ma_donhang']; ?>" class="row g-2 align-items-center mt-2">
                                <input type="hidden" name="update_order_status" value="1">
                                <div class="col-8">
                                    <select name="trang_thai" class="form-select">
                                        <?php foreach ($order_status_options as $status_option): ?>
                                            <option value="<?php echo $status_option; ?>" <?php echo ($order['trang_thai'] == $status_option) ? 'selected' : ''; ?>>
                                                <?php echo translate_status($status_option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 text-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Cập nhật</button>
                                </div>
                            </form>
                            <small class="text-muted d-block mt-2">Chuyển trạng thái qua các giai đoạn: Chờ xử lý → Đã xác nhận → Đã xuất kho → Hoàn thành hoặc Hủy.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-light">
                            <h5 class="text-secondary"><i class="fas fa-money-check-alt me-2"></i> Cập nhật trạng thái thanh toán</h5>
                            <form method="post" action="nhanvienbanhang.php?action=donhang&id=<?php echo $order['ma_donhang']; ?>" class="row g-2 align-items-center mt-2">
                                <input type="hidden" name="update_payment_status" value="1">
                                <div class="col-8">
                                    <select name="trangthai_thanhtoan" class="form-select">
                                        <?php foreach ($payment_status_options as $payment_option): ?>
                                            <option value="<?php echo $payment_option; ?>" <?php echo ($order['trangthai_thanhtoan'] == $payment_option) ? 'selected' : ''; ?>>
                                                <?php echo translate_payment_status($payment_option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 text-end">
                                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-save"></i> Cập nhật</button>
                                </div>
                            </form>
                            <small class="text-muted d-block mt-2">Cập nhật trạng thái thanh toán: Chưa thanh toán hoặc Đã thanh toán.</small>
                        </div>
                    </div>
                </div>

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
                                $ten_sanpham = (isset($item['ten_sanpham']) && $item['ten_sanpham'] !== NULL) ? $item['ten_sanpham'] : 'Không rõ tên';
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
                                    Không tìm thấy chi tiết sản phẩm.
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
                    <a href="nhanvienbanhang.php?action=donhang" class="btn btn-secondary me-3"><i class="fas fa-arrow-left"></i> Quay lại Danh sách</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> In phiếu</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        @media print {
            .sidebar, .me-3, .btn-secondary, .btn-primary, .btn-success {
                display: none !important;
            }
            .container-fluid {
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
    // Dừng chương trình tại đây
    exit;

}
// Danh sách đơn hàng
else {
    $search_term = '';
    $search_query = '';
    $params = array();

    // Xử lý tìm kiếm
    if (isset($_GET['search_term']) && trim($_GET['search_term']) !== '') {
        $search_term = trim($_GET['search_term']);

        if (is_numeric($search_term) && intval($search_term) > 0) {
            // Tìm theo ID đơn hàng
            $search_query = " AND d.ma_donhang = ? ";
            $params[] = intval($search_term);
        } else {
            // Tìm theo Tên hoặc Email
            $search_query = " AND (d.ten_nguoinhan LIKE ? OR d.email_nguoinhan LIKE ?) ";
            $params[] = '%' . $search_term . '%';
            $params[] = '%' . $search_term . '%';
        }
    }

    // Phân trang
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // Đếm tổng số đơn hàng
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

    // Lấy danh sách đơn hàng với phân trang
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
    <div class="container-fluid">
        <h2><i class="fas fa-receipt me-2"></i> Cập nhật trạng thái đơn hàng</h2>

        <!-- FORM TÌM KIẾM -->
        <div class="d-flex justify-content-end align-items-center mb-3">
            <form method="GET" action="nhanvienbanhang.php" class="d-flex">
                <input type="hidden" name="action" value="donhang">
                <input type="text" name="search_term" class="form-control me-2" placeholder="ID (chính xác) hoặc Tên/Email (tương đối)" value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($search_term)): ?>
                    <a href="nhanvienbanhang.php?action=donhang" class="btn btn-outline-danger ms-2" title="Xóa tìm kiếm"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($search_term)): ?>
            <p class="text-info">Hiển thị kết quả tìm kiếm cho: <strong><?php echo htmlspecialchars($search_term); ?></strong></p>
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
                        <td><?php echo htmlspecialchars(isset($row['sdt_khachhang']) ? $row['sdt_khachhang'] : '-'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['ngay_tao'])); ?></td>
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
                            <a href="nhanvienbanhang.php?action=donhang&id=<?php echo $row['ma_donhang']; ?>" class="btn btn-sm btn-primary" title="Xem chi tiết đơn hàng">
                                <i class="fas fa-eye"></i> Xem
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
                $query_base = 'nhanvienbanhang.php?action=donhang';
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
