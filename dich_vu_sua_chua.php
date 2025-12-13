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
    <title>Dịch Vụ Sửa Chữa Máy Đọc Sách - AkiStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .repair-hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }
        
        .repair-hero h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .repair-hero .highlight {
            color: #ff4757;
            font-size: 52px;
        }
        
        .repair-hero p {
            font-size: 24px;
            margin-bottom: 30px;
            color: #ddd;
        }
        
        .repair-hero .cta-button {
            display: inline-block;
            padding: 15px 40px;
            background: white;
            color: #1a1a2e;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .repair-hero .cta-button:hover {
            background: #088178;
            color: white;
            transform: translateY(-3px);
        }
        
        .repair-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 40px;
        }
        
        .section-title {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            color: #000;
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: #088178;
        }
        
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }
        
        .benefit-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            color: white;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .benefit-card i {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .benefit-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: white;
        }
        
        .benefit-card p {
            font-size: 18px;
            line-height: 1.6;
            color: rgba(255,255,255,0.9);
        }
        
        .process-timeline {
            position: relative;
            margin: 60px 0;
        }
        
        .process-step {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .step-number {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #088178 0%, #066d63 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            flex-shrink: 0;
            box-shadow: 0 5px 20px rgba(8,129,120,0.3);
        }
        
        .step-content {
            margin-left: 30px;
            flex: 1;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            border-left: 5px solid #088178;
        }
        
        .step-content h3 {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
        }
        
        .step-content p {
            font-size: 18px;
            color: #333;
            line-height: 1.8;
        }
        
        .pricing-table {
            width: 100%;
            border-collapse: collapse;
            margin: 40px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .pricing-table thead {
            background: linear-gradient(135deg, #088178 0%, #066d63 100%);
            color: white;
        }
        
        .pricing-table th {
            padding: 20px;
            font-size: 20px;
            font-weight: bold;
            text-align: left;
        }
        
        .pricing-table td {
            padding: 20px;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
        }
        
        .pricing-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .pricing-table .price {
            color: #ff4757;
            font-weight: bold;
            font-size: 20px;
        }
        
        .warranty-info {
            background: linear-gradient(135deg, #e7f3ff 0%, #d4edda 100%);
            padding: 40px;
            border-radius: 15px;
            margin: 40px 0;
            border-left: 6px solid #088178;
        }
        
        .warranty-info h3 {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin-bottom: 20px;
        }
        
        .warranty-info ul {
            list-style: none;
            padding: 0;
        }
        
        .warranty-info li {
            font-size: 18px;
            padding: 12px 0;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .warranty-info li i {
            color: #088178;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .contact-cta {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 60px 40px;
            border-radius: 15px;
            text-align: center;
            color: white;
            margin: 60px 0;
        }
        
        .contact-cta h2 {
            font-size: 36px;
            margin-bottom: 20px;
            color: white;
        }
        
        .contact-cta p {
            font-size: 20px;
            margin-bottom: 30px;
            color: #ddd;
        }
        
        .contact-info {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
        }
        
        .contact-item i {
            font-size: 24px;
            color: #088178;
        }
        
        @media (max-width: 768px) {
            .repair-hero h1 {
                font-size: 32px;
            }
            
            .repair-hero .highlight {
                font-size: 36px;
            }
            
            .process-step {
                flex-direction: column;
                text-align: center;
            }
            
            .step-content {
                margin-left: 0;
                margin-top: 20px;
            }
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

    <div class="repair-hero">
        <h1 style="color:#d4edda">DỊCH VỤ SỬA CHỮA</h1>
        <p>Lên đến <span class="highlight">Giảm từ 10%-30%</span> - Tất cả các dòng máy đọc sách.</p>
        <a href="#pricing" class="cta-button">Tìm hiểu thêm</a>
    </div>

    <div class="repair-container">
        <h2 class="section-title">Ưu Đãi Đặc Biệt Khi Sửa Chữa Tại AkiStore</h2>
        
        <div class="benefits-grid">
            <div class="benefit-card">
                <i class="fas fa-percentage"></i>
                <h3>Giảm Giá 10-30%</h3>
                <p>Giảm giá lên đến 30% cho dịch vụ sửa chữa màn hình, pin, bo mạch. Khách hàng thân thiết được ưu đãi thêm 5%.</p>
            </div>
            
            <div class="benefit-card">
                <i class="fas fa-tools"></i>
                <h3>Miễn Phí Kiểm Tra</h3>
                <p>Kiểm tra, chẩn đoán lỗi hoàn toàn miễn phí. Tư vấn chi tiết về tình trạng máy và phương án sửa chữa tối ưu.</p>
            </div>
            
            <div class="benefit-card">
                <i class="fas fa-shipping-fast"></i>
                <h3>Miễn Phí Vận Chuyển</h3>
                <p>Miễn phí gửi - nhận máy trong nội thành HCM, Hà Nội. Hỗ trợ chi phí vận chuyển cho các tỉnh thành khác.</p>
            </div>
            
            <div class="benefit-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Bảo Hành 6 Tháng</h3>
                <p>Bảo hành 6 tháng cho linh kiện thay thế. Cam kết sửa chữa bằng linh kiện chính hãng, chất lượng cao.</p>
            </div>
            
            <div class="benefit-card">
                <i class="fas fa-laptop"></i>
                <h3>Máy Dự Phòng Miễn Phí</h3>
                <p>Cho mượn máy đọc sách tạm thời trong thời gian sửa chữa (yêu cầu đặt cọc). Không lo gián đoạn việc đọc.</p>
            </div>
            
            <div class="benefit-card">
                <i class="fas fa-book"></i>
                <h3>Tặng Kho Sách 10.000+</h3>
                <p>Tặng kho sách điện tử miễn phí hơn 10.000 đầu sách đa thể loại. Hỗ trợ cài đặt từ điển, ứng dụng đọc sách.</p>
            </div>
        </div>

        <h2 class="section-title">Quy Trình Sửa Chữa Chuyên Nghiệp</h2>
        
        <div class="process-timeline">
            <div class="process-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Liên Hệ & Tiếp Nhận</h3>
                    <p>Liên hệ qua hotline <strong>0981523130</strong> hoặc email <strong>leduc2103@gmail.com</strong>. Mô tả lỗi, cung cấp hình ảnh/video nếu có. Chúng tôi sẽ tư vấn sơ bộ và hướng dẫn gửi máy.</p>
                </div>
            </div>
            
            <div class="process-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Kiểm Tra & Báo Giá</h3>
                    <p>Kỹ thuật viên chuyên nghiệp kiểm tra chi tiết trong vòng 1-2 giờ. Báo giá rõ ràng, minh bạch. Khách hàng xác nhận trước khi tiến hành sửa chữa.</p>
                </div>
            </div>
            
            <div class="process-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Sửa Chữa & Kiểm Tra Chất Lượng</h3>
                    <p>Sử dụng linh kiện chính hãng, công cụ chuyên dụng. Thời gian sửa chữa: 1-5 ngày tùy mức độ. Kiểm tra kỹ lưỡng trước khi trả máy.</p>
                </div>
            </div>
            
            <div class="process-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>Bàn Giao & Hướng Dẫn</h3>
                    <p>Bàn giao máy đã sửa, hướng dẫn sử dụng, bảo quản. Cấp phiếu bảo hành 6 tháng. Hỗ trợ kỹ thuật trọn đời qua điện thoại/email.</p>
                </div>
            </div>
        </div>

        <h2 class="section-title" id="pricing">Bảng Giá Sửa Chữa & Ưu Đãi</h2>
        
        <table class="pricing-table">
            <thead>
                <tr>
                    <th>Dịch Vụ Sửa Chữa</th>
                    <th>Giá Niêm Yết</th>
                    <th>Giá Ưu Đãi</th>
                    <th>Tiết Kiệm</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Thay màn hình E-Ink (6 inch)</strong></td>
                    <td>3.000.000đ</td>
                    <td class="price">2.400.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-20%</td>
                </tr>
                <tr>
                    <td><strong>Thay màn hình E-Ink (7-8 inch)</strong></td>
                    <td>4.500.000đ</td>
                    <td class="price">3.600.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-20%</td>
                </tr>
                <tr>
                    <td><strong>Thay pin</strong></td>
                    <td>800.000đ</td>
                    <td class="price">560.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-30%</td>
                </tr>
                <tr>
                    <td><strong>Sửa lỗi phần mềm, cài đặt lại hệ thống</strong></td>
                    <td>300.000đ</td>
                    <td class="price">Miễn phí</td>
                    <td style="color: #28a745; font-weight: bold;">-100%</td>
                </tr>
                <tr>
                    <td><strong>Vệ sinh, bảo dưỡng máy</strong></td>
                    <td>200.000đ</td>
                    <td class="price">Miễn phí</td>
                    <td style="color: #28a745; font-weight: bold;">-100%</td>
                </tr>
                <tr>
                    <td><strong>Thay nút nguồn, nút bấm</strong></td>
                    <td>500.000đ</td>
                    <td class="price">400.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-20%</td>
                </tr>
                <tr>
                    <td><strong>Sửa bo mạch chính</strong></td>
                    <td>2.000.000đ</td>
                    <td class="price">1.500.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-25%</td>
                </tr>
                <tr>
                    <td><strong>Thay cổng sạc, cáp sạc</strong></td>
                    <td>400.000đ</td>
                    <td class="price">320.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-20%</td>
                </tr>
                <tr>
                    <td><strong>Sửa lỗi không lên nguồn</strong></td>
                    <td>1.500.000đ</td>
                    <td class="price">1.200.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-20%</td>
                </tr>
                <tr>
                    <td><strong>Thay vỏ, viền máy</strong></td>
                    <td>600.000đ</td>
                    <td class="price">480.000đ</td>
                    <td style="color: #28a745; font-weight: bold;">-20%</td>
                </tr>
            </tbody>
        </table>

        <div class="warranty-info">
            <h3><i class="fas fa-award"></i> Cam Kết Chất Lượng Dịch Vụ</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Sử dụng 100% linh kiện chính hãng, có tem nhãn rõ ràng</li>
                <li><i class="fas fa-check-circle"></i> Kỹ thuật viên có chứng chỉ chuyên môn, kinh nghiệm 5+ năm</li>
                <li><i class="fas fa-check-circle"></i> Bảo hành 6 tháng cho linh kiện thay thế, 3 tháng cho dịch vụ sửa chữa</li>
                <li><i class="fas fa-check-circle"></i> Hoàn tiền 100% nếu không sửa được hoặc không hài lòng</li>
                <li><i class="fas fa-check-circle"></i> Minh bạch chi phí, không phát sinh thêm nếu chưa báo trước</li>
                <li><i class="fas fa-check-circle"></i> Hỗ trợ kỹ thuật trọn đời qua hotline và email</li>
            </ul>
        </div>

        <div class="contact-cta">
            <h2>Cần Hỗ Trợ Sửa Chữa? Liên Hệ Ngay!</h2>
            <p>Đội ngũ kỹ thuật chuyên nghiệp sẵn sàng hỗ trợ bạn 24/7</p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>Hotline: 0981523130</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>Email: leduc2103@gmail.com</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>124 Lê Quang Định, Bình Thạnh, TP.HCM</span>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
