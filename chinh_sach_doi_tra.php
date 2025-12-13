<?php
session_start();
require_once 'config/database.php';

// Lấy số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT SUM(so_luong) as total FROM gio_hang WHERE ma_user = ?");
        $stmt->execute(array($_SESSION['user_id']));
        $result = $stmt->fetch();
        $cart_count = $result['total'] ? $result['total'] : 0;
    } catch (Exception $e) {
        $cart_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính Sách Đổi Trả - Aki Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .policy-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 40px;
            background: white;
        }
        
        .breadcrumb {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .breadcrumb a {
            color: #088178;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .policy-title {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .policy-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 20px;
        }
        
        .policy-table th,
        .policy-table td {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: left;
            vertical-align: top;
        }
        
        .policy-table th {
            background: #f5f5f5;
            font-weight: bold;
            color: #000;
        }
        
        .policy-table tr:nth-child(even) {
            background: #fafafa;
        }
        
        .policy-table td:first-child {
            width: 40%;
            font-weight: 600;
            color: #333;
        }
        
        .policy-table td:last-child {
            width: 60%;
            line-height: 1.8;
        }
        
        .policy-table ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .policy-table li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo7.png" class="logo" width="120px" alt="Aki Shop Logo"></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="shop.php">Sản phẩm</a></li>
                <li><a href="blog.php">Tin tức</a></li>
                <li><a href="about.php">Giới thiệu</a></li>
                <li><a href="contact.php">Liên hệ</a></li>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                <li id="lg-bag" class="user-menu">
                    <a href="#"><i class="fa-solid fa-user"></i></a>
                    <div class="user-dropdown">
                        <a href="my_orders.php">Đơn hàng của tôi</a>
                        <a href="logout.php">Đăng xuất</a>
                    </div>
                </li>
                <?php endif; ?>
                <li id="lg-bag">
                    <a href="cart.php" style="position: relative;">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <a href="#" id="close"><i class="fa-solid fa-xmark"></i></a>    
            </ul> 
            <div id="mobile">
                <a href="cart.php" style="position: relative;">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <i id="bar" class="fas fa-outdent"></i>
            </div>  
        </div>
    </section>

    <div class="policy-container">
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a> / Chính sách đổi trả và bảo hành
        </div>
        
        <h1 class="policy-title">Giải đáp các câu hỏi thường gặp trong quá trình sử dụng và bảo quản sản phẩm máy đọc sách:</h1>
        
        <table class="policy-table">
            <thead>
                <tr>
                    <th>Câu hỏi</th>
                    <th>Câu trả lời</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1. Chính sách đổi trả và bảo hành của Akishop thế nào?</td>
                    <td>
                        Với những máy đọc sách mua mới (nguyên seal) sẽ được nhận bảo hành 12 tháng và 1 đổi 1 trong vòng 30 ngày nếu có lỗi thuộc về nhà sản xuất. Với sản phẩm Boox Poke 5 phiên bản 2024, thời gian bảo hành là 24 tháng.
                        <br><br>
                        Sản phẩm khi đổi trả cần phải còn nguyên vẹn, đầy đủ phụ kiện đi kèm,... Vui lòng liên hệ với tổng đài của Akishop để được hướng dẫn cụ thể nếu bạn muốn đổi trả.
                    </td>
                </tr>
                <tr>
                    <td>2. Tôi có thể nhận thêm khuyến mãi nào khi mua hàng tại Akishop không?</td>
                    <td>
                        Akishop tặng khách hàng khi mua hàng tại Akishop các kho sách miễn phí hơn 10 ngàn cuốn với nhiều thể loại khác nhau như kinh doanh, quản lý, tiểu thuyết, kĩ năng sống, Phật giáo,... kho truyện tranh. Cùng với đó là hỗ trợ cài đặt từ điển, hỗ trợ tìm sách, hỗ trợ kĩ thuật trọn đời.
                    </td>
                </tr>
                <tr>
                    <td>3. Nếu tôi không còn như cầu sử dụng, Akishop có nhận thu lại máy đọc sách cũ không?</td>
                    <td>
                        Akishop không thu mua máy đọc sách cũ của khách hàng. Tuy nhiên, Aki sẽ hỗ trợ bạn lên đời máy. Ví dụ, khi bạn muốn lên đời từ Kindle Paperwhite 5 sang Boox Go Color 7, Akishop sẽ thu lại máy cũ là Kindle Paperwhite 5 và bạn sẽ bù phần chênh lệch để nhận Boox Go Color 7.
                    </td>
                </tr>
                <tr>
                    <td>4. Những phương thức thanh toán nào được chấp nhận?</td>
                    <td>
                        Tùy theo phương thức mua hàng, bạn sẽ có những cách thanh toán như sau:
                        <ul>
                            <li>Mua hàng tại website: chuyển khoản & thanh toán khi nhận hàng (COD)</li>
                            <li>Mua hàng trực tiếp tại showroom: tiền mặt, chuyển khoản, thẻ tín dụng</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>5. Tôi có thể mua trả góp tại website này không?</td>
                    <td>
                        Akishop chỉ hỗ trợ quẹt thẻ trả góp khi mua hàng trực tiếp tại showroom, không hỗ trợ trả góp từ xa. Tuy nhiên, bạn vẫn có thể trả góp online khi mua hàng tại gian hàng của Akishop trên các sàn thương mại điện tử như Shopee.
                    </td>
                </tr>
                <tr>
                    <td>6. Thời gian giao hàng là bao lâu?</td>
                    <td>
                        Thời gian giao hàng thông thường là từ 3-5 ngày làm việc đối với các khu vực nội thành Hà Nội, Hồ Chí Minh và từ 3-5 ngày làm việc đối với các khu vực ngoại thành, phụ thuộc vào đơn vị vận chuyển và các yếu tố ngoại cảnh khác như thời tiết. Ngay khi bạn đặt hàng, Akishop sẽ đi đơn và liên hệ với bạn để bạn có thể theo dõi đơn hàng của mình.
                    </td>
                </tr>
                <tr>
                    <td>7. Xem thông tin các chương trình ưu đãi ở đâu?</td>
                    <td>
                        Chúng tôi thường xuyên có các chương trình khuyến mãi và ưu đãi dành cho khách hàng. Bạn có thể theo dõi trang web hoặc các mạng xã hội của Akishop để cập nhật thông tin về các chương trình khuyến mãi mới nhất.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
