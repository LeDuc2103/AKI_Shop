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
    <title>Điều Khoản & Điều Kiện - AkiStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .terms-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 40px;
            background: white;
        }
        
        .breadcrumb {
            font-size: 16px;
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
        
        .terms-title {
            font-size: 32px;
            font-weight: bold;
            color: #000;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .terms-content {
            font-size: 20px;
            color: #000;
            line-height: 1.8;
        }
        
        .terms-content h2 {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .terms-content h3 {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin-top: 25px;
            margin-bottom: 12px;
        }
        
        .terms-content p {
            font-size: 20px;
            color: #000;
            margin-bottom: 15px;
        }
        
        .terms-content ul,
        .terms-content ol {
            margin: 15px 0;
            padding-left: 40px;
        }
        
        .terms-content li {
            font-size: 20px;
            color: #000;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <section id="header">
        <a href="index.php"><img src="img/logo7.png" class="logo" width="120px" alt="AkiStore Logo"></a>
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

    <div class="terms-container">
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a> / Điều khoản & Điều kiện
        </div>
        
        <h1 class="terms-title">ĐIỀU KHOẢN & ĐIỀU KIỆN SỬ DỤNG DỊCH VỤ</h1>
        
        <div class="terms-content">
            <h2>1. Giới thiệu</h2>
            <p>Chào mừng bạn đến với AkiStore - nền tảng thương mại điện tử chuyên cung cấp máy đọc sách chính hãng. Khi truy cập và sử dụng website AkiStore.com.vn, bạn đã đồng ý tuân thủ và chịu ràng buộc bởi các điều khoản và điều kiện sử dụng được nêu dưới đây.</p>
            <p>Vui lòng đọc kỹ các điều khoản này trước khi sử dụng dịch vụ. Nếu bạn không đồng ý với bất kỳ phần nào của các điều khoản này, vui lòng không sử dụng website của chúng tôi.</p>

            <h2>2. Định nghĩa</h2>
            <ul>
                <li><strong>AkiStore:</strong> Là website thương mại điện tử tại địa chỉ AkiStore.com.vn, chuyên cung cấp máy đọc sách và các phụ kiện liên quan.</li>
                <li><strong>Người dùng/Khách hàng:</strong> Là cá nhân hoặc tổ chức truy cập và sử dụng dịch vụ tại AkiStore.com.vn.</li>
                <li><strong>Sản phẩm:</strong> Là các máy đọc sách, phụ kiện và dịch vụ được cung cấp trên AkiStore.com.vn.</li>
                <li><strong>Đơn hàng:</strong> Là yêu cầu mua sản phẩm được gửi từ khách hàng đến AkiStore.</li>
            </ul>

            <h2>3. Tài khoản người dùng</h2>
            <h3>3.1. Đăng ký tài khoản</h3>
            <ul>
                <li>Để sử dụng đầy đủ các tính năng của AkiStore, bạn cần đăng ký tài khoản bằng cách cung cấp thông tin chính xác, đầy đủ và cập nhật.</li>
                <li>Bạn phải từ 18 tuổi trở lên hoặc có sự đồng ý của người giám hộ hợp pháp để đăng ký tài khoản.</li>
                <li>Mỗi người chỉ được đăng ký một tài khoản duy nhất.</li>
            </ul>

            <h3>3.2. Bảo mật tài khoản</h3>
            <ul>
                <li>Bạn có trách nhiệm bảo mật thông tin tài khoản (tên đăng nhập và mật khẩu).</li>
                <li>Mọi hoạt động thực hiện dưới tài khoản của bạn sẽ được coi là do bạn thực hiện.</li>
                <li>Nếu phát hiện tài khoản bị sử dụng trái phép, vui lòng thông báo ngay cho AkiStore qua email leduc2103@gmail.com hoặc hotline 0981523130.</li>
            </ul>

            <h3>3.3. Quyền đình chỉ tài khoản</h3>
            <p>AkiStore có quyền đình chỉ hoặc khóa tài khoản của bạn nếu phát hiện các hành vi vi phạm điều khoản sử dụng, gian lận hoặc lạm dụng hệ thống.</p>

            <h2>4. Quy định về đặt hàng và thanh toán</h2>
            <h3>4.1. Đặt hàng</h3>
            <ul>
                <li>Khách hàng có thể đặt hàng trực tuyến 24/7 qua website AkiStore.com.vn.</li>
                <li>Đơn hàng chỉ được xác nhận sau khi AkiStore liên hệ xác nhận với khách hàng qua điện thoại hoặc email.</li>
                <li>AkiStore có quyền từ chối đơn hàng nếu thông tin không chính xác, sản phẩm hết hàng hoặc có dấu hiệu gian lận.</li>
            </ul>

            <h3>4.2. Giá cả và khuyến mãi</h3>
            <ul>
                <li>Giá sản phẩm hiển thị trên website đã bao gồm VAT (nếu có).</li>
                <li>Giá có thể thay đổi bất kỳ lúc nào mà không cần báo trước. Giá áp dụng là giá tại thời điểm đơn hàng được xác nhận.</li>
                <li>Các chương trình khuyến mãi có thời hạn và điều kiện áp dụng cụ thể sẽ được thông báo rõ ràng.</li>
                <li>Khuyến mãi không áp dụng đồng thời trừ khi có quy định khác.</li>
            </ul>

            <h3>4.3. Phương thức thanh toán</h3>
            <ul>
                <li><strong>Thanh toán khi nhận hàng (COD):</strong> Thanh toán bằng tiền mặt khi nhận sản phẩm.</li>
                <li><strong>Chuyển khoản ngân hàng:</strong> Chuyển khoản trực tiếp vào tài khoản của AkiStore.</li>
                <li><strong>Thanh toán online:</strong> Thanh toán qua cổng VNPAY, SePay với các phương thức thẻ ATM, thẻ tín dụng, ví điện tử.</li>
            </ul>

            <h2>5. Vận chuyển và giao nhận</h2>
            <h3>5.1. Phạm vi giao hàng</h3>
            <p>AkiStore cung cấp dịch vụ giao hàng toàn quốc thông qua các đơn vị vận chuyển uy tín.</p>

            <h3>5.2. Thời gian giao hàng</h3>
            <ul>
                <li><strong>Nội thành Hà Nội, TP.HCM:</strong> 1-3 ngày làm việc.</li>
                <li><strong>Các tỉnh thành khác:</strong> 3-5 ngày làm việc.</li>
                <li>Thời gian có thể thay đổi tùy thuộc vào điều kiện thực tế (thời tiết, địa lý, ngày lễ...).</li>
            </ul>

            <h3>5.3. Phí vận chuyển</h3>
            <ul>
                <li>Phí vận chuyển được tính dựa trên trọng lượng, kích thước sản phẩm và khoảng cách giao hàng.</li>
                <li>Phí vận chuyển sẽ được thông báo rõ ràng trước khi khách hàng xác nhận đơn hàng.</li>
                <li>AkiStore có các chương trình miễn phí vận chuyển theo từng thời điểm.</li>
            </ul>

            <h3>5.4. Trách nhiệm khi nhận hàng</h3>
            <ul>
                <li>Khách hàng có trách nhiệm kiểm tra tình trạng bên ngoài của kiện hàng trước khi nhận.</li>
                <li>Nếu phát hiện kiện hàng bị rách, móp méo, ướt hoặc có dấu hiệu bất thường, vui lòng từ chối nhận và liên hệ ngay với AkiStore.</li>
                <li>Sau khi ký nhận hàng, khách hàng vui lòng kiểm tra sản phẩm trong vòng 24 giờ và thông báo nếu có vấn đề.</li>
            </ul>

            <h2>6. Chính sách đổi trả và hoàn tiền</h2>
            <h3>6.1. Điều kiện đổi trả</h3>
            <ul>
                <li>Sản phẩm được đổi trả trong vòng 30 ngày kể từ ngày mua hàng.</li>
                <li>Sản phẩm còn nguyên vẹn, đầy đủ phụ kiện, hộp, tem, nhãn mác.</li>
                <li>Có hóa đơn mua hàng hoặc chứng từ hợp lệ.</li>
                <li>Sản phẩm bị lỗi kỹ thuật từ nhà sản xuất.</li>
            </ul>

            <h3>6.2. Trường hợp không được đổi trả</h3>
            <ul>
                <li>Sản phẩm đã qua sử dụng, có dấu hiệu va đập, trầy xước do người dùng.</li>
                <li>Sản phẩm đã bị thay đổi, sửa chữa bởi bên thứ ba không được ủy quyền.</li>
                <li>Không còn đầy đủ phụ kiện, hộp, tem niêm phong.</li>
                <li>Sản phẩm mua trong chương trình khuyến mãi đặc biệt (trừ khi có quy định khác).</li>
            </ul>

            <h3>6.3. Quy trình đổi trả</h3>
            <ol>
                <li>Liên hệ AkiStore qua hotline 0981523130 hoặc email leduc2103@gmail.com để thông báo yêu cầu đổi trả.</li>
                <li>Cung cấp thông tin đơn hàng và lý do đổi trả.</li>
                <li>AkiStore xác nhận và hướng dẫn khách hàng gửi sản phẩm về (miễn phí vận chuyển nếu lỗi từ nhà sản xuất).</li>
                <li>Sau khi nhận và kiểm tra sản phẩm, AkiStore sẽ thực hiện đổi sản phẩm mới hoặc hoàn tiền trong vòng 5-7 ngày làm việc.</li>
            </ol>

            <h2>7. Bảo hành sản phẩm</h2>
            <ul>
                <li>Tất cả sản phẩm máy đọc sách tại AkiStore đều được bảo hành chính hãng từ nhà sản xuất.</li>
                <li>Thời gian bảo hành: 12 tháng đối với hầu hết các sản phẩm (một số dòng có thể lên đến 24 tháng).</li>
                <li>Bảo hành không áp dụng cho các trường hợp: rơi vỡ, vào nước, tự ý sửa chữa, sử dụng sai mục đích.</li>
                <li>Chi tiết về chính sách bảo hành vui lòng xem tại <a href="chinhsachbaohanh.php" style="color: #088178;">Chính sách bảo hành</a>.</li>
            </ul>

            <h2>8. Quyền sở hữu trí tuệ</h2>
            <ul>
                <li>Toàn bộ nội dung trên website AkiStore.com.vn bao gồm văn bản, hình ảnh, logo, thiết kế đều thuộc quyền sở hữu của AkiStore.</li>
                <li>Nghiêm cấm mọi hành vi sao chép, sử dụng, phân phối nội dung mà không có sự cho phép bằng văn bản từ AkiStore.</li>
                <li>Vi phạm quyền sở hữu trí tuệ sẽ bị xử lý theo quy định pháp luật Việt Nam.</li>
            </ul>

            <h2>9. Giới hạn trách nhiệm</h2>
            <ul>
                <li>AkiStore không chịu trách nhiệm về các thiệt hại gián tiếp, ngẫu nhiên hoặc do hậu quả phát sinh từ việc sử dụng hoặc không thể sử dụng website.</li>
                <li>AkiStore không đảm bảo website sẽ hoạt động liên tục, không bị gián đoạn hoặc không có lỗi.</li>
                <li>Trong trường hợp bất khả kháng (thiên tai, hỏa hoạn, chiến tranh, dịch bệnh...), AkiStore được miễn trừ trách nhiệm thực hiện nghĩa vụ.</li>
            </ul>

            <h2>10. Bảo mật thông tin</h2>
            <p>AkiStore cam kết bảo mật thông tin cá nhân của khách hàng theo quy định pháp luật. Chi tiết vui lòng xem tại <a href="chinhsachbaomat.php" style="color: #088178;">Chính sách bảo mật</a>.</p>

            <h2>11. Giải quyết tranh chấp</h2>
            <ul>
                <li>Mọi tranh chấp phát sinh sẽ được ưu tiên giải quyết thông qua thương lượng, hòa giải giữa AkiStore và khách hàng.</li>
                <li>Nếu không thể giải quyết bằng thương lượng, tranh chấp sẽ được đưa ra Tòa án có thẩm quyền tại TP. Hồ Chí Minh để giải quyết.</li>
                <li>Luật điều chỉnh: Pháp luật Việt Nam.</li>
            </ul>

            <h2>12. Thay đổi điều khoản</h2>
            <ul>
                <li>AkiStore có quyền thay đổi, cập nhật các điều khoản và điều kiện sử dụng bất kỳ lúc nào.</li>
                <li>Các thay đổi sẽ có hiệu lực ngay khi được đăng tải trên website.</li>
                <li>Việc bạn tiếp tục sử dụng dịch vụ sau khi có thay đổi đồng nghĩa với việc bạn chấp nhận các điều khoản mới.</li>
            </ul>

            <h2>13. Thông tin liên hệ</h2>
            <p>Nếu bạn có bất kỳ câu hỏi nào về các Điều khoản & Điều kiện này, vui lòng liên hệ:</p>
            <ul>
                <li><strong>Công ty TNHH Thương mại và Dịch vụ Kỹ thuật Diệu Phúc</strong></li>
                <li><strong>Địa chỉ:</strong> 124 Lê Quang Định, phường Bình Thạnh, TP.HỒ CHÍ MINH, VIỆT NAM</li>
                <li><strong>Email:</strong> leduc2103@gmail.com</li>
                <li><strong>Hotline:</strong> 0981523130</li>
                <li><strong>Giờ làm việc:</strong> 8:00 - 17:00, Thứ 2 - Thứ 7</li>
            </ul>

            <p style="margin-top: 40px; text-align: center; font-style: italic;">
                <strong>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của AkiStore!</strong>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
