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
    <title>Chính Sách Bảo Hành - AkiStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .warranty-container {
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
        
        .warranty-title {
            font-size: 32px;
            font-weight: bold;
            color: #000;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .warranty-content {
            font-size: 20px;
            color: #000;
            line-height: 1.8;
        }
        
        .warranty-content h2 {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .warranty-content h3 {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin-top: 25px;
            margin-bottom: 12px;
        }
        
        .warranty-content p {
            font-size: 20px;
            color: #000;
            margin-bottom: 15px;
        }
        
        .warranty-content ul,
        .warranty-content ol {
            margin: 15px 0;
            padding-left: 40px;
        }
        
        .warranty-content li {
            font-size: 20px;
            color: #000;
            margin: 10px 0;
        }
        
        .warranty-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 20px;
        }
        
        .warranty-table th,
        .warranty-table td {
            border: 1px solid #000;
            padding: 15px;
            text-align: left;
            vertical-align: top;
        }
        
        .warranty-table th {
            background: #f5f5f5;
            font-weight: bold;
            color: #000;
            text-align: center;
        }
        
        .highlight-box {
            background: #e7f3ff;
            border-left: 4px solid #088178;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
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

    <div class="warranty-container">
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a> / Chính sách bảo hành
        </div>
        
        <h1 class="warranty-title">CHÍNH SÁCH BẢO HÀNH SẢN PHẨM MÁY ĐỌC SÁCH</h1>
        
        <div class="warranty-content">
            <div class="highlight-box">
                <p><strong><i class="fas fa-shield-alt"></i> Cam kết của AkiStore:</strong> Tất cả sản phẩm máy đọc sách tại AkiStore đều là hàng chính hãng 100%, được bảo hành theo tiêu chuẩn của nhà sản xuất và luật bảo vệ quyền lợi người tiêu dùng Việt Nam.</p>
            </div>

            <h2>1. Thời gian bảo hành</h2>
            <table class="warranty-table">
                <thead>
                    <tr>
                        <th>Loại sản phẩm</th>
                        <th>Thời gian bảo hành</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Máy đọc sách Kindle (Amazon)</td>
                        <td>12 tháng</td>
                        <td>Bảo hành chính hãng Amazon</td>
                    </tr>
                    <tr>
                        <td>Máy đọc sách Kobo</td>
                        <td>12 tháng</td>
                        <td>Bảo hành chính hãng Kobo</td>
                    </tr>
                    <tr>
                        <td>Máy đọc sách Boox (Onyx)</td>
                        <td>12-24 tháng</td>
                        <td>Boox Poke 5 (2024): 24 tháng, các dòng khác: 12 tháng</td>
                    </tr>
                    <tr>
                        <td>Máy đọc sách PocketBook</td>
                        <td>12 tháng</td>
                        <td>Bảo hành chính hãng PocketBook</td>
                    </tr>
                    <tr>
                        <td>Phụ kiện (bao da, cáp sạc, đèn đọc sách)</td>
                        <td>6 tháng</td>
                        <td>Bảo hành lỗi sản xuất</td>
                    </tr>
                </tbody>
            </table>

            <h2>2. Điều kiện được bảo hành</h2>
            <h3>2.1. Sản phẩm được bảo hành khi:</h3>
            <ul>
                <li>Sản phẩm còn trong thời hạn bảo hành (tính từ ngày mua hàng ghi trên hóa đơn).</li>
                <li>Có phiếu bảo hành hoặc hóa đơn mua hàng hợp lệ từ AkiStore.</li>
                <li>Tem bảo hành của AkiStore và nhà sản xuất còn nguyên vẹn.</li>
                <li>Lỗi kỹ thuật do nhà sản xuất (màn hình chết điểm, lỗi phần mềm, lỗi phần cứng...).</li>
                <li>Sản phẩm bị lỗi trong quá trình sử dụng bình thường theo hướng dẫn.</li>
            </ul>

            <h3>2.2. Chế độ bảo hành 1 đổi 1 trong 30 ngày đầu:</h3>
            <div class="highlight-box">
                <p><strong>Đặc biệt:</strong> Với máy đọc sách mua mới (nguyên seal), AkiStore áp dụng chính sách <strong>"1 ĐỔI 1 TRONG 30 NGÀY"</strong> nếu phát hiện lỗi kỹ thuật do nhà sản xuất.</p>
                <p><strong>Điều kiện:</strong></p>
                <ul style="margin-bottom: 0;">
                    <li>Sản phẩm còn nguyên vẹn, không trầy xước, móp méo, vỡ màn hình.</li>
                    <li>Đầy đủ phụ kiện ban đầu (hộp, sách hướng dẫn, cáp sạc...).</li>
                    <li>Có hóa đơn mua hàng từ AkiStore.</li>
                </ul>
            </div>

            <h2>3. Trường hợp KHÔNG được bảo hành</h2>
            <div class="warning-box">
                <p><strong><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng:</strong> Các trường hợp sau KHÔNG được bảo hành miễn phí:</p>
            </div>
            <ul>
                <li><strong>Hư hỏng do người dùng:</strong>
                    <ul>
                        <li>Rơi vỡ, va đập mạnh làm vỡ màn hình, biến dạng thân máy.</li>
                        <li>Vào nước, ngấm nước, ẩm ướt do không bảo quản đúng cách.</li>
                        <li>Cháy nổ do sử dụng nguồn điện không phù hợp.</li>
                        <li>Trầy xước nặng, bong tróc sơn do va đập.</li>
                    </ul>
                </li>
                <li><strong>Can thiệp trái phép:</strong>
                    <ul>
                        <li>Tự ý tháo máy, sửa chữa tại cơ sở không được ủy quyền.</li>
                        <li>Thay thế linh kiện không chính hãng.</li>
                        <li>Cài đặt phần mềm, firmware không chính thức.</li>
                        <li>Tem bảo hành bị rách, bong tróc hoặc có dấu hiệu can thiệp.</li>
                    </ul>
                </li>
                <li><strong>Hao mòn tự nhiên:</strong>
                    <ul>
                        <li>Pin chai, giảm dung lượng do sử dụng lâu (trên 12 tháng).</li>
                        <li>Ố vàng màn hình do thời gian.</li>
                        <li>Mờ chữ trên phím bấm do sử dụng nhiều.</li>
                    </ul>
                </li>
                <li><strong>Các trường hợp khác:</strong>
                    <ul>
                        <li>Sản phẩm hết hạn bảo hành.</li>
                        <li>Không có hóa đơn hoặc phiếu bảo hành hợp lệ.</li>
                        <li>Số serial trên máy không khớp với phiếu bảo hành.</li>
                        <li>Lỗi phần mềm do người dùng tự cài đặt.</li>
                    </ul>
                </li>
            </ul>

            <h2>4. Quy trình bảo hành</h2>
            <h3>Bước 1: Liên hệ với AkiStore</h3>
            <ul>
                <li><strong>Hotline:</strong> 0981523130 (8:00 - 17:00, Thứ 2 - Thứ 7)</li>
                <li><strong>Email:</strong> leduc2103@gmail.com</li>
                <li><strong>Địa chỉ:</strong> 124 Lê Quang Định, phường Bình Thạnh, TP.HỒ CHÍ MINH</li>
            </ul>
            <p>Cung cấp thông tin: Số hóa đơn, tên sản phẩm, mô tả lỗi, hình ảnh/video lỗi (nếu có).</p>

            <h3>Bước 2: Gửi sản phẩm bảo hành</h3>
            <p><strong>Cách 1:</strong> Mang trực tiếp sản phẩm đến cửa hàng AkiStore (khuyến khích để được hỗ trợ nhanh nhất).</p>
            <p><strong>Cách 2:</strong> Gửi qua đơn vị vận chuyển:</p>
            <ul>
                <li>AkiStore chịu phí vận chuyển 2 chiều nếu lỗi thuộc nhà sản xuất.</li>
                <li>Khách hàng chịu phí vận chuyển nếu sản phẩm không thuộc diện bảo hành.</li>
                <li>Đóng gói cẩn thận, chèn xốp, ghi rõ "DỄ VỠ - THẬN TRỌNG".</li>
            </ul>

            <h3>Bước 3: Kiểm tra và xử lý</h3>
            <ul>
                <li>Bộ phận kỹ thuật AkiStore sẽ kiểm tra sản phẩm trong vòng 1-2 ngày làm việc.</li>
                <li>Thông báo kết quả kiểm tra cho khách hàng (thuộc bảo hành hay không, chi phí sửa chữa nếu có).</li>
                <li>Nếu khách hàng đồng ý, tiến hành sửa chữa/thay thế.</li>
            </ul>

            <h3>Bước 4: Nhận sản phẩm</h3>
            <ul>
                <li><strong>Thời gian xử lý:</strong> 5-15 ngày làm việc tùy mức độ lỗi.</li>
                <li><strong>Đối với lỗi phần mềm đơn giản:</strong> 1-2 ngày.</li>
                <li><strong>Đối với lỗi phần cứng phức tạp:</strong> 10-15 ngày (có thể cần gửi về hãng).</li>
                <li>Khách hàng nhận sản phẩm đã sửa chữa kèm phiếu bảo hành mới (nếu có).</li>
            </ul>

            <h2>5. Chi phí bảo hành</h2>
            <table class="warranty-table">
                <thead>
                    <tr>
                        <th>Tình trạng</th>
                        <th>Chi phí</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Lỗi thuộc nhà sản xuất (trong thời hạn bảo hành)</td>
                        <td><strong style="color: #088178;">MIỄN PHÍ 100%</strong></td>
                    </tr>
                    <tr>
                        <td>Lỗi do người dùng (rơi vỡ, vào nước...)</td>
                        <td>Báo giá sau khi kiểm tra (từ 200.000đ - 5.000.000đ tùy mức độ)</td>
                    </tr>
                    <tr>
                        <td>Thay thế linh kiện (màn hình, pin, bo mạch...)</td>
                        <td>Theo giá niêm yết của hãng + phí công thay thế</td>
                    </tr>
                    <tr>
                        <td>Vệ sinh, bảo dưỡng định kỳ</td>
                        <td>Miễn phí (khách hàng chỉ chịu phí vận chuyển nếu gửi từ xa)</td>
                    </tr>
                </tbody>
            </table>

            <h2>6. Chính sách hỗ trợ đặc biệt</h2>
            <div class="highlight-box">
                <h3 style="margin-top: 0;">6.1. Hỗ trợ kỹ thuật trọn đời</h3>
                <ul style="margin-bottom: 0;">
                    <li>Hướng dẫn sử dụng máy đọc sách, cài đặt từ điển, chuyển file sách.</li>
                    <li>Tư vấn cài đặt ứng dụng đọc sách, tối ưu hóa hiển thị.</li>
                    <li>Hỗ trợ xử lý các lỗi phần mềm cơ bản qua điện thoại/email.</li>
                    <li>Cung cấp kho sách miễn phí (hơn 10.000 đầu sách đa thể loại).</li>
                </ul>
            </div>

            <div class="highlight-box">
                <h3 style="margin-top: 0;">6.2. Chương trình máy đọc sách dự phòng</h3>
                <p>Đối với khách hàng có nhu cầu sử dụng liên tục, trong thời gian bảo hành (từ 5-15 ngày), AkiStore hỗ trợ cho mượn máy đọc sách tạm thời (yêu cầu đặt cọc).</p>
            </div>

            <div class="highlight-box">
                <h3 style="margin-top: 0;">6.3. Chính sách lên đời máy</h3>
                <ul style="margin-bottom: 0;">
                    <li>Khách hàng muốn nâng cấp lên dòng máy cao cấp hơn có thể đổi máy cũ lấy máy mới.</li>
                    <li>AkiStore thu lại máy cũ với giá hợp lý, khách hàng bù chênh lệch.</li>
                    <li>Ví dụ: Đổi Kindle Paperwhite 5 lấy Boox Go Color 7, chỉ cần bù thêm phần chênh lệch.</li>
                </ul>
            </div>

            <h2>7. Lưu ý quan trọng</h2>
            <ul>
                <li><strong>Sao lưu dữ liệu:</strong> Trước khi gửi bảo hành, vui lòng sao lưu toàn bộ dữ liệu (sách, ghi chú, bookmark...). AkiStore không chịu trách nhiệm về mất mát dữ liệu trong quá trình bảo hành.</li>
                <li><strong>Bảo quản phiếu bảo hành:</strong> Giữ gìn phiếu bảo hành và hóa đơn mua hàng cẩn thận, đây là căn cứ để được bảo hành.</li>
                <li><strong>Sử dụng đúng cách:</strong> Đọc kỹ hướng dẫn sử dụng, không sử dụng sai mục đích để tránh hư hỏng.</li>
                <li><strong>Bảo vệ màn hình:</strong> Nên sử dụng bao da, miếng dán màn hình để bảo vệ máy khỏi va đập.</li>
                <li><strong>Nguồn điện:</strong> Chỉ sử dụng bộ sạc chính hãng hoặc bộ sạc được AkiStore khuyến nghị.</li>
            </ul>

            <h2>8. Cam kết chất lượng dịch vụ</h2>
            <ul>
                <li><i class="fas fa-check-circle" style="color: #088178;"></i> <strong>100% sản phẩm chính hãng</strong> - Xuất hóa đơn VAT đầy đủ.</li>
                <li><i class="fas fa-check-circle" style="color: #088178;"></i> <strong>Bảo hành uy tín</strong> - Cam kết thực hiện đúng chính sách đã công bố.</li>
                <li><i class="fas fa-check-circle" style="color: #088178;"></i> <strong>Hỗ trợ nhiệt tình</strong> - Đội ngũ kỹ thuật chuyên nghiệp, tư vấn tận tâm.</li>
                <li><i class="fas fa-check-circle" style="color: #088178;"></i> <strong>Quy trình nhanh chóng</strong> - Xử lý bảo hành trong thời gian ngắn nhất.</li>
                <li><i class="fas fa-check-circle" style="color: #088178;"></i> <strong>Minh bạch chi phí</strong> - Báo giá rõ ràng trước khi sửa chữa.</li>
            </ul>

            <h2>9. Thông tin liên hệ bảo hành</h2>
            <div class="highlight-box">
                <p><strong>TRUNG TÂM BẢO HÀNH AKISTORE</strong></p>
                <ul style="margin-bottom: 0;">
                    <li><i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> 124 Lê Quang Định, phường Bình Thạnh, TP.HỒ CHÍ MINH, VIỆT NAM</li>
                    <li><i class="fas fa-phone"></i> <strong>Hotline:</strong> 0981523130</li>
                    <li><i class="fas fa-envelope"></i> <strong>Email:</strong> leduc2103@gmail.com</li>
                    <li><i class="fas fa-clock"></i> <strong>Giờ làm việc:</strong> 8:00 - 17:00 (Thứ 2 - Thứ 7)</li>
                    <li><i class="fas fa-globe"></i> <strong>Website:</strong> www.AkiStore.com.vn</li>
                </ul>
            </div>

            <p style="margin-top: 40px; text-align: center; font-style: italic; color: #088178;">
                <strong><i class="fas fa-heart"></i> Cảm ơn quý khách đã tin tưởng và lựa chọn sản phẩm tại AkiStore!</strong><br>
                <strong>Chúng tôi cam kết mang đến trải nghiệm đọc sách tuyệt vời nhất!</strong>
            </p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
