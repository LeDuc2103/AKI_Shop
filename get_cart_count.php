<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Tính tổng số lượng sản phẩm trong giỏ hàng
$total_items = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (isset($item['quantity'])) {
            $total_items += $item['quantity'];
        }
    }
}

echo json_encode([
    'success' => true,
    'count' => $total_items
]);
?>
