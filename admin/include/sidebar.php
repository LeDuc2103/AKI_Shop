<?php
// Kiểm tra thông tin admin để hiển thị tên
if (isset($adminInfo)) {
?>
    <div class="text-center text-white p-3 border-bottom">
        <i class="fas fa-user-shield fa-2x mb-2"></i>
        <h5><?php echo htmlspecialchars($adminInfo['ho_ten']); ?></h5>
        <small><?php echo htmlspecialchars($adminInfo['vai_tro']); ?></small>
    </div>
<?php
}
?>
<a href="index.php"><i class="fas fa-home me-2"></i> Trang chủ</a>
<a href="admin.php?action=dashboard"><i class="fas fa-tachometer-alt me-2"></i> Thống kê</a>
<a href="admin.php?action=sanpham"><i class="fas fa-box-open me-2"></i> Sản phẩm</a>
<a href="admin.php?action=nhanvien"><i class="fas fa-user-tie me-2"></i> Nhân viên</a>
<a href="admin.php?action=khachhang"><i class="fas fa-user-friends me-2"></i> Khách hàng</a>
<a href="admin.php?action=donhang"><i class="fas fa-file-invoice-dollar me-2"></i> Đơn hàng</a>
<a href="admin.php?action=danhmuc"><i class="fas fa-list me-2"></i> Danh mục</a>
<a href="admin.php?action=khuyenmai"><i class="fas fa-gift me-2"></i> Quản lý khuyến mãi</a>
<a href="admin.php?action=hoan_tien"><i class="fas fa-money-bill-wave me-2"></i> Hoàn tiền</a>
<a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Đăng xuất</a>
