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


// Lọc năm, tháng và ngày mặc định
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$day = isset($_GET['day']) ? intval($_GET['day']) : null;
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all'; // all, day, month, year

// Xây dựng WHERE clause cho filter
$where_filter = "";
$params_filter = array();

if ($filter_type == 'year' && $year) {
    $where_filter = "AND YEAR(created_at) = ?";
    $params_filter[] = $year;
} elseif ($filter_type == 'month' && $year && $month) {
    $where_filter = "AND YEAR(created_at) = ? AND MONTH(created_at) = ?";
    $params_filter[] = $year;
    $params_filter[] = $month;
} elseif ($filter_type == 'day' && $year && $month && $day) {
    $where_filter = "AND YEAR(created_at) = ? AND MONTH(created_at) = ? AND DAY(created_at) = ?";
    $params_filter[] = $year;
    $params_filter[] = $month;
    $params_filter[] = $day;
}

// === Thống kê tổng quan (có lọc) ===

// Tổng khách hàng (Lọc theo vai_tro và ngày tạo)
$sql_kh = "SELECT COUNT(*) AS total FROM user WHERE LOWER(vai_tro) = 'khachhang' $where_filter";
$stmt = $conn->prepare($sql_kh);
if (!empty($params_filter)) {
    $stmt->execute($params_filter);
} else {
    $stmt->execute();
}
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['khachhang'] = isset($row['total']) ? $row['total'] : 0;

// Tổng sản phẩm (không lọc theo thời gian)
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM san_pham");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['sanpham'] = isset($row['total']) ? $row['total'] : 0;

// Tổng đơn hàng (có lọc)
$sql_dh = "SELECT COUNT(*) AS total FROM don_hang WHERE 1=1 $where_filter";
$stmt = $conn->prepare($sql_dh);
if (!empty($params_filter)) {
    $stmt->execute($params_filter);
} else {
    $stmt->execute();
}
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['donhang'] = isset($row['total']) ? $row['total'] : 0;

// *** LOGIC DOANH THU: Tính theo tien_hang và trạng thái 'hoan_thanh' (có lọc) ***
$sql_dt = "SELECT SUM(tien_hang) AS total FROM don_hang WHERE trang_thai = 'hoan_thanh' $where_filter";
$stmt = $conn->prepare($sql_dt);
if (!empty($params_filter)) {
    $stmt->execute($params_filter);
} else {
    $stmt->execute();
}
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

// === TOP KHÁCH HÀNG MUA NHIỀU NHẤT ===
// Phân trang
$page_top_customer = isset($_GET['page_top']) ? intval($_GET['page_top']) : 1;
$limit_top = 5;
$offset_top = ($page_top_customer - 1) * $limit_top;

// Đếm tổng số khách hàng có đơn hàng
$stmt_count = $conn->prepare("
    SELECT COUNT(DISTINCT u.ma_user) as total
    FROM user u
    INNER JOIN don_hang dh ON u.ma_user = dh.ma_user
    WHERE LOWER(u.vai_tro) = 'khachhang'
");
$stmt_count->execute();
$row_count = $stmt_count->fetch(PDO::FETCH_ASSOC);
$total_customers = $row_count['total'];
$total_pages_top = ceil($total_customers / $limit_top);

// Lấy top khách hàng mua nhiều nhất
$stmt_top_customers = $conn->prepare("
    SELECT 
        u.ma_user,
        u.ho_ten,
        u.email,
        u.phone,
        COUNT(DISTINCT dh.ma_donhang) as so_don_hang,
        SUM(dh.tong_tien) as tong_tien_mua,
        MAX(dh.created_at) as lan_mua_gan_nhat
    FROM user u
    INNER JOIN don_hang dh ON u.ma_user = dh.ma_user
    WHERE LOWER(u.vai_tro) = 'khachhang'
    GROUP BY u.ma_user, u.ho_ten, u.email, u.phone
    ORDER BY tong_tien_mua DESC
    LIMIT ".$limit_top." OFFSET ".$offset_top."
");
$stmt_top_customers->execute(); 
$top_customers = $stmt_top_customers->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-line me-2"></i> Thống kê tổng quan</h2>
        
        <!-- Bộ lọc thống kê -->
        <form method="get" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="action" value="dashboard">
            
            <select name="filter_type" id="filterType" class="form-select" style="width: 150px;">
                <option value="all" <?php echo $filter_type == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                <!-- <option value="day" <?php echo $filter_type == 'day' ? 'selected' : ''; ?>>Theo ngày</option> -->
                <option value="month" <?php echo $filter_type == 'month' ? 'selected' : ''; ?>>Theo tháng</option>
                <option value="year" <?php echo $filter_type == 'year' ? 'selected' : ''; ?>>Theo năm</option>
            </select>
            
            <select name="day" id="dayFilter" class="form-select" style="width: 100px; <?php echo $filter_type != 'day' ? 'display:none;' : ''; ?>">
                <?php
                for ($d = 1; $d <= 31; $d++) {
                    $sel = ($d == $day) ? 'selected' : '';
                    echo "<option value='$d' $sel>Ngày $d</option>";
                }
                ?>
            </select>
            
            <select name="month" id="monthFilter" class="form-select" style="width: 120px; <?php echo $filter_type == 'year' || $filter_type == 'all' ? 'display:none;' : ''; ?>">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $sel = ($m == $month) ? 'selected' : '';
                    echo "<option value='$m' $sel>Tháng $m</option>";
                }
                ?>
            </select>
            
            <select name="year" id="yearFilter" class="form-select" style="width: 100px; <?php echo $filter_type == 'all' ? 'display:none;' : ''; ?>">
                <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= $currentYear - 10; $y--) {
                    $sel = ($y == $year) ? 'selected' : '';
                    echo "<option value='$y' $sel>$y</option>";
                }
                ?>
            </select>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
        </form>
    </div>
    
    <script>
    document.getElementById('filterType').addEventListener('change', function() {
        const filterType = this.value;
        const dayFilter = document.getElementById('dayFilter');
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');
        
        if (filterType === 'all') {
            dayFilter.style.display = 'none';
            monthFilter.style.display = 'none';
            yearFilter.style.display = 'none';
        } else if (filterType === 'day') {
            dayFilter.style.display = 'block';
            monthFilter.style.display = 'block';
            yearFilter.style.display = 'block';
        } else if (filterType === 'month') {
            dayFilter.style.display = 'none';
            monthFilter.style.display = 'block';
            yearFilter.style.display = 'block';
        } else if (filterType === 'year') {
            dayFilter.style.display = 'none';
            monthFilter.style.display = 'none';
            yearFilter.style.display = 'block';
        }
    });
    </script>

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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-chart-column me-2"></i> Doanh thu theo tháng (<?php echo $year; ?>)</h4>
        <form method="get" class="d-flex gap-2" id="monthYearFilter">
            <input type="hidden" name="action" value="dashboard">
            <select name="year" class="form-select" onchange="this.form.submit()" style="width: 100px;">
                <?php
                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                    $sel = ($y == $year) ? 'selected' : '';
                    echo "<option value='$y' $sel>$y</option>";
                }
                ?>
            </select>
            <select name="month" class="form-select" onchange="this.form.submit()" style="width: 120px;">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $sel = ($m == $month) ? 'selected' : '';
                    echo "<option value='$m' $sel>Tháng $m</option>";
                }
                ?>
            </select>
        </form>
    </div>
    <canvas id="chartThang" height="100"></canvas>

    <hr class="my-5">

    <div class="mb-3">
        <h4><i class="fas fa-calendar-day me-2"></i> Doanh thu theo ngày (Tháng <?php echo $month; ?>/<?php echo $year; ?>)</h4>
        <small class="text-muted">Sử dụng bộ lọc ở phần "Doanh thu theo tháng" bên trên để thay đổi tháng/năm hiển thị</small>
    </div>
    <canvas id="chartNgay" height="100"></canvas>
    
    <hr class="my-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h4 class="m-0 font-weight-bold text-primary"><i class="fas fa-trophy me-1"></i> Top Khách hàng mua nhiều nhất</h4>
                </div>
                <div class="card-body">
                    <?php if (count($top_customers) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" width="100%" cellspacing="0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Khách hàng</th>
                                    <th>Email</th>
                                    <th>SĐT</th>
                                    <th>Số đơn hàng</th>
                                    <th>Tổng tiền mua</th>
                                    <th>Lần mua gần nhất</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = $offset_top + 1;
                                foreach ($top_customers as $customer): 
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-warning">
                                            <?php 
                                            if ($rank == 1) echo '<i class="fas fa-crown text-warning"></i> ';
                                            echo $rank;
                                            ?>
                                        </strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($customer['ho_ten']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars(isset($customer['phone']) ? $customer['phone'] : 'N/A'); ?></td>
                                    <td><span class="badge bg-info"><?php echo number_format($customer['so_don_hang']); ?></span></td>
                                    <td><strong class="text-success"><?php echo number_format($customer['tong_tien_mua'], 0, ',', '.'); ?>₫</strong></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($customer['lan_mua_gan_nhat'])); ?></td>
                                </tr>
                                <?php 
                                $rank++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Phân trang -->
                    <?php if ($total_pages_top > 1): ?>
                    <nav aria-label="Phân trang khách hàng">
                        <ul class="pagination justify-content-center mt-3">
                            <?php if ($page_top_customer > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=dashboard&page_top=<?php echo $page_top_customer - 1; ?><?php if($filter_type != 'all') echo '&filter_type='.$filter_type.'&year='.$year.'&month='.$month.($day ? '&day='.$day : ''); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages_top; $i++): ?>
                            <li class="page-item <?php echo $i == $page_top_customer ? 'active' : ''; ?>">
                                <a class="page-link" href="?action=dashboard&page_top=<?php echo $i; ?><?php if($filter_type != 'all') echo '&filter_type='.$filter_type.'&year='.$year.'&month='.$month.($day ? '&day='.$day : ''); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_top_customer < $total_pages_top): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=dashboard&page_top=<?php echo $page_top_customer + 1; ?><?php if($filter_type != 'all') echo '&filter_type='.$filter_type.'&year='.$year.'&month='.$month.($day ? '&day='.$day : ''); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <?php else: ?>
                        <div class="alert alert-info text-center">Chưa có khách hàng nào mua hàng.</div>
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