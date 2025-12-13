<?php
session_start();
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!isset($conn) || !($conn instanceof PDO)) {
    echo "<!DOCTYPE html><html><head><title>Lỗi Kết Nối</title></head><body><div style='color: red; text-align: center; margin-top: 50px;'>Lỗi: Biến \$conn không phải là đối tượng PDO.</div></body></html>";
    exit;
}

// Tạo cột trang_thai_kho nếu chưa có
try {
    $check_column = $conn->query("SHOW COLUMNS FROM don_hang_doi_tra LIKE 'trang_thai_kho'");
    if ($check_column->rowCount() == 0) {
        $conn->exec("ALTER TABLE don_hang_doi_tra ADD COLUMN trang_thai_kho ENUM('cho_nhap_kho', 'da_nhap_kho') DEFAULT 'cho_nhap_kho' AFTER status");
    }
} catch (PDOException $e) {
    // Bỏ qua nếu cột đã tồn tại
}

if (!function_exists('translate_status')) {
    function translate_status($status) {
        switch ($status) {
            case 'cho_xu_ly': return 'Chờ xử lý';
            case 'xac_nhan': return 'Đã xác nhận';
            case 'da_xuat_kho': return 'Đã xuất kho';
            case 'hoan_thanh': return 'Hoàn thành';
            case 'huy': return 'Đã hủy';
            case 'pending': return 'Chờ xử lý';
            case 'approved': return 'Đã duyệt';
            case 'rejected': return 'Từ chối';
            default: return $status;
        }
    }
}

function translate_warehouse_status($status) {
    switch ($status) {
        case 'cho_nhap_kho': return 'Chờ nhập kho';
        case 'da_nhap_kho': return 'Đã nhập kho';
        default: return $status;
    }
}

$message = '';
$ma_donhang_chi_tiet = 0;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ma_donhang_chi_tiet = intval($_GET['id']);
}
$action_type = isset($_GET['action_type']) ? $_GET['action_type'] : '';

// Xử lý cập nhật trạng thái nhập kho
if ($action_type == 'update_nhap_kho' && $ma_donhang_chi_tiet > 0) {
    try {
        $stmt_update = $conn->prepare("UPDATE don_hang_doi_tra SET trang_thai_kho = 'da_nhap_kho', updated_at = NOW() WHERE ma_donhang = ? AND status = 'approved' AND trang_thai_kho = 'cho_nhap_kho'");
        $stmt_update->execute(array($ma_donhang_chi_tiet));

        if ($stmt_update->rowCount() > 0) {
            $message = "<div class='alert alert-success'>Đã xác nhận nhập kho đơn hàng #" . $ma_donhang_chi_tiet . " thành công!</div>";
        } else {
            $message = "<div class='alert alert-warning'>Không thể cập nhật trạng thái hoặc đơn hàng không ở trạng thái phù hợp.</div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>Lỗi CSDL: " . $e->getMessage() . "</div>";
    }
    echo "<script>window.location.href = 'nhanvienkho_nhap.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Nhập Kho - Đổi Trả</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; }
        .card { border-radius: 0.75rem; }
        .table-striped tbody tr:nth-child(odd) { background-color: #f8f8f8; }
        @media print {
            .navbar, .btn-secondary, .btn-primary, .action-btn { display: none !important; }
            .container { width: 100% !important; padding: 0 !important; margin: 0 !important; }
            .card { box-shadow: none !important; border: none !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="bg-green-600 shadow-md py-4 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white"><i class="fas fa-warehouse mr-2"></i> Nhập Kho Đổi Trả</h1>
        <div class="flex gap-4">
            <a href="nhanvienkho.php" class="text-white hover:text-green-200 transition duration-150">
                <i class="fas fa-box mr-1"></i> Xuất Kho
            </a>
            <a href="logout.php" class="text-white hover:text-green-200 transition duration-150">
                <i class="fas fa-sign-out-alt mr-1"></i> Đăng xuất
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <?php 
    if (isset($message)) { echo $message; }

    // CHẾ ĐỘ CHI TIẾT ĐƠN HÀNG ĐỔI TRẢ
    if ($ma_donhang_chi_tiet > 0) {
        $stmt_return = $conn->prepare("
            SELECT 
                ddt.*,
                dh.ten_nguoinhan,
                dh.so_dienthoai,
                dh.diachi_nhan,
                dh.tong_tien,
                dh.created_at AS ngay_tao,
                u.ho_ten AS ten_khach
            FROM don_hang_doi_tra ddt
            LEFT JOIN don_hang dh ON ddt.ma_donhang = dh.ma_donhang
            LEFT JOIN user u ON ddt.ma_user = u.ma_user
            WHERE ddt.ma_donhang = ?
        ");
        $stmt_return->execute(array($ma_donhang_chi_tiet));
        $return_order = $stmt_return->fetch(PDO::FETCH_ASSOC);

        if (!$return_order) {
            echo "<div class='alert alert-danger p-4 bg-red-100 text-red-700 rounded-lg'>Không tìm thấy đơn đổi trả #" . $ma_donhang_chi_tiet . ".</div>";
        } else {
            // Lấy chi tiết sản phẩm
            $stmt_details = $conn->prepare("
                SELECT
                    ct.so_luong,
                    ct.don_gia,
                    ct.id_sanpham,
                    sp.ten_sanpham
                FROM chitiet_donhang ct
                LEFT JOIN san_pham sp ON ct.id_sanpham = sp.id_sanpham
                WHERE ct.ma_donhang = ?
            ");
            $stmt_details->execute(array($ma_donhang_chi_tiet));
            $order_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="card bg-white shadow-xl p-6">
                <h2 class="text-center mb-6 text-3xl font-extrabold text-green-700">
                    <i class="fas fa-undo mr-2"></i> Chi tiết Đơn Đổi Trả #<?php echo htmlspecialchars($return_order['ma_donhang']); ?>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-4 border rounded-lg bg-gray-50">
                    <div>
                        <h3 class="text-lg font-semibold text-green-600 mb-2"><i class="fas fa-user-check mr-2"></i> Thông tin Khách hàng</h3>
                        <p><strong>Tên khách:</strong> <?php echo htmlspecialchars($return_order['ten_khach']); ?></p>
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($return_order['ten_nguoinhan']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars($return_order['so_dienthoai']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($return_order['diachi_nhan']); ?></p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-green-600 mb-2"><i class="fas fa-info-circle mr-2"></i> Thông tin Đổi Trả</h3>
                        <p><strong>Trạng thái:</strong> <span class="px-2 py-1 rounded bg-green-100 text-green-800"><?php echo translate_status($return_order['status']); ?></span></p>
                        <p><strong>Trạng thái kho:</strong> <span class="px-2 py-1 rounded <?php echo $return_order['trang_thai_kho'] == 'da_nhap_kho' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'; ?>"><?php echo translate_warehouse_status($return_order['trang_thai_kho']); ?></span></p>
                        <p><strong>Ngày yêu cầu:</strong> <?php echo date('d/m/Y H:i', strtotime($return_order['created_at'])); ?></p>
                        <p><strong>Lý do:</strong> <?php echo nl2br(htmlspecialchars($return_order['ly_do'])); ?></p>
                    </div>
                </div>

                <h3 class="text-xl font-semibold text-green-600 mb-4"><i class="fas fa-list mr-2"></i> Danh sách sản phẩm cần nhập kho</h3>
                <table class="table-auto w-full border-collapse border border-gray-300 text-sm table-striped">
                    <thead class="bg-green-600 text-white">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">Tên sản phẩm</th>
                            <th class="border border-gray-300 px-4 py-2 text-center">Số lượng</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Đơn giá</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        foreach ($order_details as $detail): 
                            $subtotal = $detail['so_luong'] * $detail['don_gia'];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($detail['ten_sanpham']); ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-center"><?php echo $detail['so_luong']; ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right"><?php echo number_format($detail['don_gia'], 0, ',', '.'); ?>₫</td>
                            <td class="border border-gray-300 px-4 py-2 text-right font-semibold"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="bg-green-100 font-bold">
                            <td colspan="3" class="border border-gray-300 px-4 py-2 text-right">Tổng cộng:</td>
                            <td class="border border-gray-300 px-4 py-2 text-right text-green-700"><?php echo number_format($total, 0, ',', '.'); ?>₫</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-6 flex gap-3 justify-end no-print">
                    <a href="nhanvienkho_nhap.php" class="btn-secondary px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i> Quay lại
                    </a>
                    <?php if ($return_order['status'] == 'approved' && $return_order['trang_thai_kho'] == 'cho_nhap_kho'): ?>
                    <a href="nhanvienkho_nhap.php?action_type=update_nhap_kho&id=<?php echo $ma_donhang_chi_tiet; ?>" 
                       onclick="return confirm('Xác nhận đã nhập kho đơn hàng đổi trả #<?php echo $ma_donhang_chi_tiet; ?>?');"
                       class="btn-primary px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check-circle mr-2"></i> Xác nhận Đã Nhập Kho
                    </a>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn-secondary px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-print mr-2"></i> In Phiếu
                    </button>
                </div>
            </div>
            <?php
        }
    } else {
        // DANH SÁCH ĐƠN HÀNG ĐỔI TRẢ CHỜ NHẬP KHO
        $stmt_list = $conn->query("
            SELECT 
                ddt.*,
                dh.ten_nguoinhan,
                dh.tong_tien,
                u.ho_ten AS ten_khach
            FROM don_hang_doi_tra ddt
            LEFT JOIN don_hang dh ON ddt.ma_donhang = dh.ma_donhang
            LEFT JOIN user u ON ddt.ma_user = u.ma_user
            WHERE ddt.status = 'approved'
            ORDER BY 
                CASE ddt.trang_thai_kho 
                    WHEN 'cho_nhap_kho' THEN 1 
                    WHEN 'da_nhap_kho' THEN 2 
                END,
                ddt.id DESC
        ");
        $return_orders = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="card bg-white shadow-xl p-6">
            <h2 class="text-2xl font-bold mb-6 text-green-700">
                <i class="fas fa-list-ul mr-2"></i> Danh sách Đơn Đổi Trả - Nhập Kho
            </h2>

            <?php if (empty($return_orders)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox fa-3x mb-4"></i>
                    <p class="text-lg">Không có đơn đổi trả nào cần nhập kho</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse border border-gray-300 text-sm table-striped">
                        <thead class="bg-green-600 text-white">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2">#</th>
                                <th class="border border-gray-300 px-4 py-2">Mã ĐH</th>
                                <th class="border border-gray-300 px-4 py-2">Khách hàng</th>
                                <th class="border border-gray-300 px-4 py-2">Lý do</th>
                                <th class="border border-gray-300 px-4 py-2">Tổng tiền</th>
                                <th class="border border-gray-300 px-4 py-2">Trạng thái Kho</th>
                                <th class="border border-gray-300 px-4 py-2">Ngày yêu cầu</th>
                                <th class="border border-gray-300 px-4 py-2 no-print">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($return_orders as $index => $order): ?>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2 text-center"><?php echo $index + 1; ?></td>
                                <td class="border border-gray-300 px-4 py-2 text-center font-semibold">#<?php echo $order['ma_donhang']; ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($order['ten_khach']); ?></td>
                                <td class="border border-gray-300 px-4 py-2" style="max-width: 200px;">
                                    <div style="max-height: 60px; overflow-y: auto;">
                                        <?php echo nl2br(htmlspecialchars($order['ly_do'])); ?>
                                    </div>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-right"><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>₫</td>
                                <td class="border border-gray-300 px-4 py-2 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $order['trang_thai_kho'] == 'da_nhap_kho' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo translate_warehouse_status($order['trang_thai_kho']); ?>
                                    </span>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-center"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td class="border border-gray-300 px-4 py-2 text-center no-print">
                                    <a href="nhanvienkho_nhap.php?id=<?php echo $order['ma_donhang']; ?>" 
                                       class="text-green-600 hover:text-green-800 font-semibold">
                                        <i class="fas fa-eye mr-1"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    ?>
</div>

</body>
</html>
