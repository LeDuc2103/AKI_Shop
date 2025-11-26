<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Lấy thông tin sản phẩm từ POST
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;

// Validate dữ liệu
if ($product_id <= 0 || $quantity <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Kiểm tra sản phẩm đã có trong giỏ hàng chưa
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] == $product_id) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

// Nếu chưa có, thêm mới
if (!$found) {
    $_SESSION['cart'][] = array(
        'id' => $product_id,
        'quantity' => $quantity,
        'price' => $price
    );
}

// Tính tổng số lượng sản phẩm trong giỏ
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['quantity'];
}

echo json_encode([
    'success' => true,
    'message' => 'Đã thêm sản phẩm vào giỏ hàng',
    'cart_count' => $total_items
]);
?>
