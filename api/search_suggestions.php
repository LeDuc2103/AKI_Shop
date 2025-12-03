<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    
    if (empty($keyword)) {
        echo json_encode(array('suggestions' => array()));
        exit;
    }
    
    // Tìm kiếm sản phẩm theo tên sản phẩm và tên danh mục
    $sql = "SELECT DISTINCT 
                sp.id_sanpham,
                sp.ten_sanpham,
                sp.hinh_anh,
                sp.gia,
                sp.gia_khuyen_mai,
                dm.ten_danhmuc
            FROM san_pham sp
            LEFT JOIN danh_muc dm ON sp.id_danhmuc = dm.id_danhmuc
            WHERE sp.ten_sanpham LIKE :keyword 
               OR dm.ten_danhmuc LIKE :keyword
            ORDER BY 
                CASE 
                    WHEN sp.ten_sanpham LIKE :keyword_exact THEN 1
                    WHEN sp.ten_sanpham LIKE :keyword_start THEN 2
                    ELSE 3
                END,
                sp.ten_sanpham ASC
            LIMIT 8";
    
    $stmt = $conn->prepare($sql);
    $search_param = '%' . $keyword . '%';
    $search_exact = $keyword;
    $search_start = $keyword . '%';
    
    $stmt->bindParam(':keyword', $search_param, PDO::PARAM_STR);
    $stmt->bindParam(':keyword_exact', $search_exact, PDO::PARAM_STR);
    $stmt->bindParam(':keyword_start', $search_start, PDO::PARAM_STR);
    $stmt->execute();
    
    $suggestions = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $suggestions[] = array(
            'id' => $row['id_sanpham'],
            'name' => $row['ten_sanpham'],
            'category' => $row['ten_danhmuc'],
            'image' => $row['hinh_anh'],
            'price' => $row['gia'],
            'sale_price' => $row['gia_khuyen_mai']
        );
    }
    
    echo json_encode(array('suggestions' => $suggestions), JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    echo json_encode(array('suggestions' => array(), 'error' => $e->getMessage()));
}
?>
