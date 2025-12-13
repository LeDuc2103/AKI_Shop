<?php

session_start();
require_once 'config/database.php';
// Lấy đối tượng kết nối PDO (biến $conn) từ đối tượng $db
$db = new Database();
$conn = $db->getConnection();

// Kiểm tra biến $conn phải tồn tại sau khi include
if (!isset($conn) || !($conn instanceof PDO)) {
    echo "<!DOCTYPE html><html><head><title>Lỗi Kết Nối</title></head><body><div style='color: red; text-align: center; margin-top: 50px;'>Lỗi: Biến \$conn không phải là đối tượng PDO. Vui lòng kiểm tra nội dung file config/database.php.</div></body></html>";
    exit;
}

// --- 0. HÀM VÀ THIẾT LẬP CƠ BẢN ---
if (!function_exists('translate_status')) {
    function translate_status($status) {
        switch ($status) {
            case 'cho_xu_ly': return 'Chờ xử lý';
            case 'xac_nhan': return 'Đã xác nhận';
            case 'da_xuat_kho': return 'Đã xuất kho';
            case 'hoan_thanh': return 'Hoàn thành';
            case 'huy': return 'Đã hủy';
            case 'da_thanh_toan': return 'Đã thanh toán';
            case 'chua_thanh_toan': return 'Chưa thanh toán';
            default: return $status;
        }
    }
}

// Lấy ID đơn hàng cho chế độ chi tiết hoặc cập nhật
$ma_donhang_chi_tiet = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ma_donhang_chi_tiet = intval($_GET['id']);
}
$action_type = isset($_GET['action_type']) ? $_GET['action_type'] : '';


// --- 1. XỬ LÝ CẬP NHẬT TRẠNG THÁI (Action) ---
if ($action_type == 'update_status' && $ma_donhang_chi_tiet > 0 && isset($_GET['new_status'])) {
    $new_status = $_GET['new_status'];
    
    // Chỉ cho phép chuyển từ 'xac_nhan' sang 'da_xuat_kho'
    if ($new_status == 'da_xuat_kho') {
        try {
            // PHP < 5.3 fix: Sử dụng NOW()
            $stmt_update = $conn->prepare("UPDATE don_hang SET trang_thai = 'da_xuat_kho', update_at = NOW() WHERE ma_donhang = ? AND trang_thai = 'xac_nhan'");
            $stmt_update->execute(array($ma_donhang_chi_tiet));

            if ($stmt_update->rowCount() > 0) {
                 $message = "<div class='alert alert-success'>Đã cập nhật đơn hàng #" . $ma_donhang_chi_tiet . " thành **Đã xuất kho**. Đơn hàng này sẽ không còn hiển thị trong danh sách chờ xuất.</div>";
            } else {
                 $message = "<div class='alert alert-warning'>Không thể cập nhật trạng thái hoặc đơn hàng không ở trạng thái Xác nhận.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Lỗi CSDL khi cập nhật: " . $e->getMessage() . "</div>";
        }
        // Chuyển hướng để xóa tham số GET, tránh lỗi submit lại
        echo "<script>window.location.href = 'nhanvienkho.php';</script>";
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Kho - Đơn hàng chờ xuất</title>
    <!-- Thêm Tailwind CSS để có giao diện đẹp và responsive -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/mobile-responsive.js?v=1765636816"></script>
    <!-- Thêm Font Awesome cho icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css?v=1765636816">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; }
        .card { border-radius: 0.75rem; }
        .table-striped tbody tr:nth-child(odd) { background-color: #f8f8f8; }
        /* CSS cho chế độ In (Tương tự như file donhang.php) */
        @media print {
            .navbar, .btn-secondary, .btn-primary, .action-btn { display: none !important; }
            .container { width: 100% !important; padding: 0 !important; margin: 0 !important; }
            .card { box-shadow: none !important; border: none !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="bg-indigo-600 shadow-md py-4 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white"><i class="fas fa-warehouse mr-2"></i> Hệ thống Kho</h1>
        <div class="flex gap-4">
            <a href="nhanvienkho_nhap.php" class="text-white hover:text-indigo-200 transition duration-150">
                <i class="fas fa-undo mr-1"></i> Nhập Kho (Đổi Trả)
            </a>
            <a href="logout.php" class="text-white hover:text-indigo-200 transition duration-150">
                <i class="fas fa-sign-out-alt mr-1"></i> Đăng xuất
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <?php 
    // Hiển thị thông báo nếu có
    if (isset($message)) { echo $message; } 

    // --- CHẾ ĐỘ 1: XUẤT PHIẾU ĐƠN HÀNG CHI TIẾT ---
    if ($ma_donhang_chi_tiet > 0) {
        
        // 1. Lấy thông tin chung của Đơn hàng (từ bảng don_hang)
        $stmt_order = $conn->prepare("
            SELECT 
                d.*, 
                d.created_at AS ngay_tao
            FROM don_hang d
            WHERE d.ma_donhang = ?
        ");
        $stmt_order->execute(array($ma_donhang_chi_tiet));
        $order = $stmt_order->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo "<div class='alert alert-danger p-4 bg-red-100 text-red-700 rounded-lg'>Không tìm thấy đơn hàng #" . $ma_donhang_chi_tiet . ".</div>";
        } else {
            // 2. LẤY CHI TIẾT SẢN PHẨM (Sử dụng LEFT JOIN đã sửa lỗi)
            $stmt_details = $conn->prepare("
                SELECT
                    ct.so_luong,
                    ct.don_gia,
                    ct.id_sanpham,
                    sp.ten_sanpham
                FROM chitiet_donhang ct
                LEFT JOIN san_pham sp ON ct.id_sanpham = sp.id_sanpham /* Dùng LEFT JOIN */
                WHERE ct.ma_donhang = ?
            ");
            $stmt_details->execute(array($ma_donhang_chi_tiet));
            $order_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
            
            // HIỂN THỊ PHIẾU CHI TIẾT
            ?>
            <div class="card bg-white shadow-xl p-6">
                <h2 class="text-center mb-6 text-3xl font-extrabold text-indigo-700">
                    <i class="fas fa-box-open mr-2"></i> Chi tiết Đơn hàng Xuất Kho #<?php echo htmlspecialchars($order['ma_donhang']); ?>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-4 border rounded-lg bg-gray-50">
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-600 mb-2"><i class="fas fa-user-check mr-2"></i> Thông tin Người nhận</h3>
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['ten_nguoinhan']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['so_dienthoaiv']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['diachi_nhan']); ?></p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-600 mb-2"><i class="fas fa-file-invoice-dollar mr-2"></i> Tóm tắt Đơn hàng</h3>
                        <p><strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></p>
                        <p><strong>Tổng tiền:</strong> <span class="text-red-600 font-bold"><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>₫</span></p>
                        <p><strong>Trạng thái:</strong> <span class="font-bold text-<?php echo ($order['trang_thai'] == 'da_xuat_kho' ? 'green' : 'yellow'); ?>-600"><?php echo translate_status($order['trang_thai']); ?></span></p>
                    </div>
                </div>

                <h3 class="text-xl font-semibold text-gray-700 mb-3 border-b pb-2"><i class="fas fa-list-ul mr-2"></i> Danh sách Sản phẩm</h3>
                
                <table class="min-w-full divide-y divide-gray-200 shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-indigo-500 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tên sản phẩm</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Đơn giá</th>
                            <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">SL</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $i = 1;
                        if (!empty($order_details)):
                            foreach ($order_details as $item):
                                $don_gia = (isset($item['don_gia']) && $item['don_gia'] !== NULL) ? $item['don_gia'] : 0;
                                $so_luong = (isset($item['so_luong']) && $item['so_luong'] !== NULL) ? $item['so_luong'] : 0;
                                $subtotal = $so_luong * $don_gia;
                                
                                // Xử lý tên sản phẩm bị NULL
                                if (isset($item['ten_sanpham']) && $item['ten_sanpham'] !== NULL) {
                                    $ten_sanpham = $item['ten_sanpham'];
                                } else {
                                    $ten_sanpham = '<span class="text-red-500 font-medium">Sản phẩm ID #' . $item['id_sanpham'] . ' (Đã xóa)</span>';
                                }
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $i++; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $ten_sanpham; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right"><?php echo number_format($don_gia, 0, ',', '.'); ?>₫</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center"><?php echo $so_luong; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</td>
                            </tr>
                            <?php 
                            endforeach; 
                        else:
                            ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-red-600 font-medium">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>Không tìm thấy chi tiết sản phẩm trong đơn hàng này.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="mt-8 pt-4 border-t border-gray-200 text-center">
                    <a href="nhanvienkho.php" class="btn bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-150 mr-4">
                        <i class="fas fa-arrow-left"></i> Quay lại Danh sách
                    </a>
                    <?php if ($order['trang_thai'] == 'xac_nhan'): ?>
                        <a href="nhanvienkho.php?action_type=update_status&id=<?php echo $order['ma_donhang']; ?>&new_status=da_xuat_kho" 
                           class="btn bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-150" 
                           onclick="return confirm('Xác nhận Đã đóng gói và Xuất kho đơn hàng #<?php echo $order['ma_donhang']; ?>?');">
                            <i class="fas fa-truck"></i> Xác nhận Xuất kho
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php
        } // End if (!$order)

    } 
    // --- CHẾ ĐỘ 2: DANH SÁCH ĐƠN HÀNG CẦN XỬ LÝ (MẶC ĐỊNH) ---
    else {

        // 1. LẤY DANH SÁCH ĐƠN HÀNG
        // *** ĐÃ CHỈNH SỬA ***: Chỉ lấy đơn hàng có trạng thái 'xac_nhan'
        $sql = "
            SELECT 
                ma_donhang, 
                created_at AS ngay_tao,      
                tong_tien, 
                trang_thai, 
                ten_nguoinhan, 
                diachi_nhan
            FROM don_hang 
            WHERE trang_thai IN ('xac_nhan') /* CHỈ HIỂN THỊ ĐƠN CHỜ XÁC NHẬN/ĐÓNG GÓI */
            ORDER BY ma_donhang ASC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array()); 
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. HIỂN THỊ DANH SÁCH
        ?>
        <div class="card bg-white shadow-xl p-6">
            <h2 class="text-3xl font-extrabold text-indigo-700 mb-6 border-b pb-3">
                <i class="fas fa-pallet mr-2"></i> Danh sách Đơn hàng Chờ Xuất Kho
            </h2>
            
            <table class="min-w-full divide-y divide-gray-200 shadow-lg rounded-lg overflow-hidden">
                <thead class="bg-indigo-500 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Người nhận</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Địa chỉ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Ngày tạo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Tổng tiền</th>
                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 font-medium">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>Không có đơn hàng nào chờ xuất kho. Tuyệt vời!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['ma_donhang']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium"><?php echo htmlspecialchars($row['ten_nguoinhan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['diachi_nhan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo $row['ngay_tao']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium"><?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>₫</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php 
                                $status_text = translate_status($row['trang_thai']);
                                $status_class = 'bg-yellow-400 text-gray-800'; // Luôn là 'xac_nhan'
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?> text-white">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center action-btn">
                                <a href="nhanvienkho.php?id=<?php echo $row['ma_donhang']; ?>" class="text-indigo-600 hover:text-indigo-900 transition duration-150 mr-3" title="Xem chi tiết đơn hàng">
                                    <i class="fas fa-search-plus"></i> Chi tiết
                                </a>
                                <?php if ($row['trang_thai'] == 'xac_nhan'): ?>
                                    <a href="nhanvienkho.php?action_type=update_status&id=<?php echo $row['ma_donhang']; ?>&new_status=da_xuat_kho" 
                                       class="text-green-600 hover:text-green-900 transition duration-150 font-medium" 
                                       onclick="return confirm('Xác nhận Đã đóng gói và Xuất kho đơn hàng #<?php echo $row['ma_donhang']; ?>?');"
                                       title="Đóng gói và Xuất kho">
                                        <i class="fas fa-truck"></i> Xuất kho
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    } // End Chế độ 2
    ?>

</div>

</body>
</html>