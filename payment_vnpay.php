<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit();
}

// Redirect về trang thông báo
header('Location: payment_cod.php');
exit();
?>
