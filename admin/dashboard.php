<?php
if (!isset($conn)) {
    require_once '../config/database.php';
}

/**
 * Hàm chuyển đổi trạng thái (enum) sang tiếng Việt dễ đọc.
 * Dựa trên cấu trúc enum bạn đã cung cấp: 'cho_xu_ly', 'xac_nhan', 'da_xuat_kho', 'hoan_thanh', 'huy'
 */
function convert_trang_thai($status) {
    switch (strtolower($status)) {
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


// Lọc năm và tháng mặc định
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// === Thống kê tổng quan ===

// Tổng khách hàng (Lọc theo vai_tro)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM user WHERE LOWER(vai_tro) = 'khachhang'");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['khachhang'] = isset($row['total']) ? $row['total'] : 0;

// Tổng sản phẩm
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM san_pham");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['sanpham'] = isset($row['total']) ? $row['total'] : 0;

// Tổng đơn hàng (Bao gồm tất cả trạng thái)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM don_hang");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['donhang'] = isset($row['total']) ? $row['total'] : 0;

// *** LOGIC DOANH THU: Tính theo tien_hang và trạng thái 'hoan_thanh' ***
$stmt = $conn->prepare("SELECT SUM(tien_hang) AS total FROM don_hang WHERE trang_thai = 'hoan_thanh'");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['doanhthu'] = isset($row['total']) ? $row['total'] : 0;


// === Biểu đồ theo quý ===
$stmt = $conn->prepare("
    SELECT QUARTER(created_at) AS quy, SUM(tien_hang) AS doanhthu
    FROM don_hang
    WHERE trang_thai = 'hoan_thanh' AND YEAR(created_at) = ?
    GROUP BY QUARTER(created_at)
");
$stmt->execute(array($year));
$chart_quy = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $chart_quy[intval($row['quy'])] = floatval($row['doanhthu']);
}

// === Biểu đồ theo tháng ===
$stmt = $conn->prepare("
    SELECT MONTH(created_at) AS thang, SUM(tien_hang) AS doanhthu
    FROM don_hang
    WHERE trang_thai = 'hoan_thanh' AND YEAR(created_at) = ?
    GROUP BY MONTH(created_at)
");
$stmt->execute(array($year));
$chart_thang = array_fill(1, 12, 0);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $chart_thang[intval($row['thang'])] = floatval($row['doanhthu']);
}

// === Biểu đồ theo ngày (chọn tháng & năm) ===
$stmt = $conn->prepare("
    SELECT DAY(created_at) AS ngay, SUM(tien_hang) AS doanhthu
    FROM don_hang
    WHERE trang_thai = 'hoan_thanh' 
      AND YEAR(created_at) = ? AND MONTH(created_at) = ?
    GROUP BY DAY(created_at)
");
$stmt->execute(array($year, $month));
$chart_ngay = array_fill(1, 31, 0);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $chart_ngay[intval($row['ngay'])] = floatval($row['doanhthu']);
}

// === LẤY 5 ĐƠN HÀNG GẦN NHẤT (ĐÃ DÙNG CỘT ho_ten CHÍNH XÁC) ===
$stmt_recent_orders = $conn->prepare("
    SELECT dh.*, u.ho_ten  
    FROM don_hang dh
    JOIN user u ON dh.ma_user = u.ma_user
    ORDER BY dh.ma_donhang ASC 
    LIMIT 5
");
$stmt_recent_orders->execute(); 
$recent_orders = $stmt_recent_orders->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">
    <h2><i class="fas fa-chart-line me-2"></i> Thống kê tổng quan</h2>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-bg-primary text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h5>Khách hàng</h5>
                    <h3><?php echo number_format($stats['khachhang']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success text-center">
                <div class="card-body">
                    <i class="fas fa-box-open fa-2x mb-2"></i>
                    <h5>Sản phẩm</h5>
                    <h3><?php echo number_format($stats['sanpham']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning text-center">
                <div class="card-body">
                    <i class="fas fa-receipt fa-2x mb-2"></i>
                    <h5>Đơn hàng</h5>
                    <h3><?php echo number_format($stats['donhang']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-danger text-center">
                <div class="card-body">
                    <i class="fas fa-coins fa-2x mb-2"></i>
                    <h5>Doanh thu</h5>
                    <h3><?php echo number_format($stats['doanhthu'], 0, ',', '.'); ?>₫</h3>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-chart-bar me-2"></i> Doanh thu theo quý (<?php echo $year; ?>)</h4>
        <form method="get" class="d-flex">
            <input type="hidden" name="action" value="dashboard">
            <select name="year" class="form-select" onchange="this.form.submit()">
                <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                    $sel = ($y == $year) ? 'selected' : '';
                    echo "<option value='$y' $sel>$y</option>";
                }
                ?>
            </select>
        </form>
    </div>
    <canvas id="chartQuy" height="100"></canvas>

    <hr class="my-5">

    <h4><i class="fas fa-chart-column me-2"></i> Doanh thu theo tháng (<?php echo $year; ?>)</h4>
    <canvas id="chartThang" height="100"></canvas>

    <hr class="my-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-calendar-day me-2"></i> Doanh thu theo ngày</h4>
        <form method="get" class="d-flex gap-2">
            <input type="hidden" name="action" value="dashboard">
            <select name="year" class="form-select" onchange="this.form.submit()">
                <?php
                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                    $sel = ($y == $year) ? 'selected' : '';
                    echo "<option value='$y' $sel>$y</option>";
                }
                ?>
            </select>
            <select name="month" class="form-select" onchange="this.form.submit()">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $sel = ($m == $month) ? 'selected' : '';
                    echo "<option value='$m' $sel>Tháng $m</option>";
                }
                ?>
            </select>
        </form>
    </div>
    <canvas id="chartNgay" height="100"></canvas>
    
    <hr class="my-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h4 class="m-0 font-weight-bold text-primary"><i class="fas fa-history me-1"></i> 5 Đơn hàng Gần nhất</h4>
                </div>
                <div class="card-body">
                    <?php if (count($recent_orders) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" width="100%" cellspacing="0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['ma_donhang']); ?></td>
                                    <td><?php echo htmlspecialchars($order['ho_ten']); ?></td>
                                    <td><?php echo number_format($order['tong_tien'], 0, ',', '.'); ?>₫</td>
                                    <td>
                                        <strong><?php echo convert_trang_thai($order['trang_thai']); ?></strong>
                                    </td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">Chưa có đơn hàng nào được ghi nhận.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('chartQuy'), {
    type: 'bar',
    data: {
        labels: ['Quý 1','Quý 2','Quý 3','Quý 4'],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [<?php echo implode(',', $chart_quy); ?>],
            backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e']
        }]
    }
});

new Chart(document.getElementById('chartThang'), {
    type: 'line',
    data: {
        labels: [<?php for($i=1;$i<=12;$i++){echo "'Tháng $i'".($i<12?',':'');} ?>],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [<?php echo implode(',', $chart_thang); ?>],
            borderColor: '#36b9cc',
            fill: false,
            tension: 0.3
        }]
    }
});

new Chart(document.getElementById('chartNgay'), {
    type: 'bar',
    data: {
        labels: [<?php for($i=1;$i<=31;$i++){echo "'$i'".($i<31?',':'');} ?>],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [<?php echo implode(',', $chart_ngay); ?>],
            backgroundColor: '#1cc88a'
        }]
    }
});
</script>