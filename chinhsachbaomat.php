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
    <title>Chính Sách Bảo Mật - Aki Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .policy-container {
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
        
        .policy-title {
            font-size: 32px;
            font-weight: bold;
            color: #000;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .policy-content {
            font-size: 20px;
            color: #000;
            line-height: 1.8;
        }
        
        .policy-content h2 {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .policy-content h3 {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin-top: 25px;
            margin-bottom: 12px;
        }
        
        .policy-content p {
            font-size: 20px;
            color: #000;
            margin-bottom: 15px;
        }
        
        .policy-content ul,
        .policy-content ol {
            margin: 15px 0;
            padding-left: 40px;
        }
        
        .policy-content li {
            font-size: 20px;
            color: #000;
            margin: 10px 0;
        }
        
        .special-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 20px;
        }
        
        .special-table th,
        .special-table td {
            border: 1px solid #000;
            padding: 15px;
            text-align: left;
            vertical-align: top;
        }
        
        .special-table th {
            background: #f5f5f5;
            font-weight: bold;
            color: #000;
            text-align: center;
        }
        
        .special-table ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .special-table li {
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
            <a href="index.php">Trang chủ</a> / Chính sách bảo mật thông tin khách hàng
        </div>
        
        <h1 class="policy-title">PHẦN V. CHÍNH SÁCH BẢO MẬT THÔNG TIN KHÁCH HÀNG</h1>
        
        <div class="policy-content">
            <h2>1. Sự chấp thuận</h2>
            <p>Bằng việc truy cập vào và sử dụng một số dịch vụ tại website Akishop.com.vn, bạn đồng ý rằng thông tin cá nhân của bạn sẽ được thu thập và sử dụng như được nêu trong Chính Sách này. Trường hợp bạn không đồng ý với Chính sách này, bạn có thể dừng cung cấp thông tin cho chúng tôi và/hoặc sử dụng các quyền như được nêu tại Mục V dưới đây.</p>

            <h2>2. Phạm vi thu thập</h2>
            <ul>
                <li>Khi truy cập và sử dụng một số dịch vụ tại Akishop.com.vn, bạn có thể sẽ được yêu cầu cung cấp trực tiếp cho chúng tôi thông tin cá nhân (Email, Họ tên, Số ĐT liên lạc, địa chỉ, thông tin đăng nhập tài khoản bao gồm thông tin bất kỳ cần thiết để thiết lập tài khoản ví dụ như tên đăng nhập, mật khẩu đăng nhập, ID/địa chỉ đăng nhập và câu hỏi/trả lời an ninh …). Mọi thông tin khai báo phải đảm bảo tính chính xác và hợp pháp. Akishop.com.vn không chịu mọi trách nhiệm liên quan đến tính pháp lý của thông tin khai báo.</li>
                <li>Chúng tôi cũng có thể thu thập thông tin về số lần truy cập, bao gồm số trang bạn xem, số links (liên kết) bạn click và những thông tin khác liên quan đến việc kết nối đến site Akishop.com.vn.</li>
                <li>Chúng tôi cũng thu thập các thông tin từ trình duyệt Web (Browser) bạn sử dụng mỗi khi truy cập vào Akishop.com.vn, bao gồm: địa chỉ IP, loại Browser, ngôn ngữ sử dụng, thời gian và những địa chỉ mà Browser truy xuất đến.</li>
                <li>Chúng tôi có thể sẽ thu thập thông tin cá nhân từ các nguồn hợp pháp khác.</li>
            </ul>

            <h2>3. Mục đích thu thập và sử dụng thông tin</h2>
            <p>Akishop.com.vn thu thập và sử dụng thông tin cá nhân với mục đích phù hợp và hoàn toàn tuân thủ nội dung của "Chính sách bảo mật" này. Cụ thể:</p>
            <p>Khi cần thiết, chúng tôi có thể sử dụng những thông tin này vào các mục đích:</p>
            <ul>
                <li>Xử lý đơn hàng: gọi điện/tin nhắn xác nhận việc đặt hàng, thông báo về trạng thái đơn hàng & thời gian giao hàng, xác nhận việc huỷ đơn hàng (nếu có) và xử lý các vấn đề khác liên quan đến đơn đặt hàng của bạn.</li>
                <li>Tạo và duy trì tài khoản: để tạo và duy trì tài khoản của bạn tại website của chúng tôi.</li>
                <li>Gửi thư ngỏ/thư cảm ơn, giới thiệu sản phẩm mới, dịch vụ mới hoặc các chương trình khuyến mãi của Akishop.com.vn</li>
                <li>Gửi thông tin về bảo hành sản phẩm.</li>
                <li>Phản hồi, giải quyết khiếu nại, yêu cầu của bạn.</li>
                <li>Thông tin trao thưởng (của Akishop.com.vn hoặc của hãng).</li>
                <li>Gửi thông tin cho công ty tài chính để tiếp nhận, thẩm định & duyệt hồ sơ trả góp.</li>
                <li>Các khảo sát để chăm sóc khách hàng tốt hơn.</li>
                <li>Xác nhận các thông tin về kỹ thuật & bảo mật thông tin khách hàng.</li>
                <li>Cá nhân hóa: Chúng tôi có thể tổ hợp dữ liệu được thu thập để có một cái nhìn hoàn chỉnh hơn về một người tiêu dùng và từ đó cho phép chúng tôi phục vụ tốt hơn với sự cá nhân hóa mạnh hơn ở các khía cạnh, bao gồm nhưng không giới hạn: (i) để cải thiện và cá nhân hóa trải nghiệm của bạn trên website TMĐT Akishop.com.vn, (ii) để cải thiện các tiện ích, dịch vụ, điều chỉnh chúng phù hợp với các nhu cầu được cá thể hóa và đi đến những ý tưởng dịch vụ mới, (iii) để phục vụ bạn với những giới thiệu, quảng cáo được điều chỉnh.</li>
                <li>An ninh: cho các mục đích ngăn ngừa các hoạt động phá hủy tài khoản người dùng của khách hàng hoặc các hoạt động giả mạo khách hàng.</li>
                <li>Các trường hợp có sự yêu cầu của cơ quan nhà nước có thẩm quyền, theo đúng quy định của pháp luật.</li>
            </ul>

            <h2>4. Thời gian lưu trữ thông tin</h2>
            <p>Dữ liệu cá nhân của Khách hàng sẽ được lưu trữ cho đến khi có yêu cầu hủy bỏ. Còn lại trong mọi trường hợp thông tin cá nhân khách hàng sẽ được bảo mật trên máy chủ của Akishop.com.vn.</p>

            <h2>5. Đơn vị thu thập và quản lý thông tin cá nhân:</h2>
            <p><strong>Công ty TNHH Thương mại và Dịch vụ Kỹ thuật Diệu Phúc</strong></p>
            <ul>
                <li>Giấy chứng nhận đăng ký doanh nghiệp số 0316172372 do Sở kế hoạch và Đầu tư thành phố Hồ Chí Minh cấp, đăng ký lần đầu ngày 02/03/2020</li>
                <li>Địa chỉ đăng ký kinh doanh: 350-352 Võ Văn Kiệt, Phường Cầu Ông Lãnh, TP. HCM.</li>
                <li>Điện thoại văn phòng: 1800.2063</li>
            </ul>

            <h2>6. Quyền của Khách hàng đối với thông tin cá nhân - Phương tiện và công cụ để Khách hàng tiếp cận và chỉnh sửa, xóa dữ liệu cá nhân</h2>
            <p>Khách hàng có quyền cung cấp thông tin cá nhân cho chúng tôi và có thể thay đổi quyết định đó vào bất cứ lúc nào.</p>
            <p>Khách hàng có quyền chỉnh sửa một số thông tin cơ bản bao gồm tên, giới tính, địa chỉ. Đối với các thông tin quan trọng khác như ngày tháng năm sinh, email, số điện thoại thì việc cập nhật và chỉnh sửa, xóa dữ liệu cá nhân dựa vào yêu cầu của khách hàng bằng các hình thức sau:</p>
            <ul>
                <li>Gọi điện thoại đến tổng đài chăm sóc khách hàng 1800.2063; hoặc</li>
                <li>Để lại bình luận hoặc gửi góp ý trực tiếp từ website www.Akishop.com.vn</li>
            </ul>
            <p>Khi nhận được yêu cầu từ Khách hàng, Akishop sẽ kiểm tra thông tin và liên lạc với Khách hàng để xác nhận thông tin, thông báo cho Khách hàng biết những rủi ro, ảnh hưởng hoặc thiệt hại có thể xảy ra từ việc chỉnh sửa, xóa dữ liệu đó. Trường hợp sau khi được thông báo về những rủi ro, ảnh hưởng và thiệt hại có thể xảy ra này, Khách hàng vẫn muốn chỉnh sửa, xóa dữ liệu cá nhân thì Akishop sẽ tiến hành chỉnh sửa/xóa thông tin Khách hàng khi các thông tin đã được xác minh đầy đủ và chính xác, trừ trường hợp pháp luật có quy định khác.</p>

            <h2>7. Cam kết bảo mật thông tin cá nhân khách hàng và an toàn dữ liệu</h2>
            <ul>
                <li>Khách hàng có quyền yêu cầu thay đổi hoặc huỷ bỏ thông tin cá nhân của mình.</li>
                <li>Thông tin cá nhân của khách hàng trên Akishop.com.vn được Akishop.com.vn cam kết bảo mật tuyệt đối theo chính sách bảo vệ thông tin cá nhân của Akishop.com.vn. Việc thu thập và sử dụng thông tin của mỗi khách hàng chỉ được thực hiện khi có sự đồng ý của khách hàng đó trừ những trường hợp pháp luật có quy định khác.</li>
                <li>Chỉ sử dụng thông tin khách hàng vào các nội dung ở mục (III) và cung cấp cho các bên có thể tiếp cận Dữ liệu cá nhân của Quý Khách Hàng như sau:
                    <ul>
                        <li>Các đối tác giao hàng, viễn thông, cung cấp dịch vụ tin nhắn qua internet, đối tác là công ty tài chính, doanh nghiệp bảo hiểm, thương nhân kinh doanh dịch vụ khuyến mại, thương nhân phối hợp với Akishop thực hiện chương trình khuyến mại, đối tác khác mà Akishop hợp tác tại từng thời điểm nhằm thực hiện các công việc theo phạm vi nêu tại mục (III);</li>
                        <li>Các Hãng/Nhà Phân phối để bảo hành sản phẩm, trao thưởng, thực hiện chương trình khuyến mại hoặc trường hợp khác phục vụ cho việc mua bán hàng hóa giữa Akishop và Khách hàng</li>
                        <li>Công ty liên kết của Akishop (công ty mẹ, công ty con và/hoặc các công ty cùng chịu chung sự kiểm soát, chi phối với Akishop);</li>
                        <li>Luật sư, cố vấn, đơn vị kiểm toán của Akishop;</li>
                        <li>Bên xử lý Dữ liệu cá nhân cho Akishop theo Hợp đồng cung cấp dịch vụ tại từng thời điểm;</li>
                        <li>Các đối tác trong hoạt động marketing của Akishop;</li>
                        <li>Các cơ quan nhà nước có thẩm quyền.</li>
                        <li>Các bên thứ ba khác nếu được sự đồng ý của Quý Khách Hàng tại từng thời điểm.</li>
                    </ul>
                </li>
                <li>Chúng tôi chỉ cung cấp các thông tin trong giới hạn phạm vi thông tin cần thiết và cũng sẽ yêu cầu các bên thứ ba trên tuân thủ mọi quy định pháp luật về bảo vệ thông tin cá nhân liên quan và các yêu cầu về an ninh liên quan đến thông tin cá nhân.</li>
                <li>Chúng tôi cam kết sử dụng các biện pháp quản lý, kỹ thuật phù hợp để bảo vệ thông tin do mình thu thập, lưu trữ; tuân thủ các tiêu chuẩn, quy chuẩn kỹ thuật về bảo đảm an toàn thông tin mạng.</li>
                <li>Chúng tôi lưu trữ thông tin cá nhân khách hàng trong môi trường vận hành an toàn và chỉ có nhân viên, đại diện và nhà cung cấp dịch vụ có thể truy cập trên cơ sở cần phải biết. Chúng tôi tuân theo các tiêu chuẩn ngành, pháp luật trong việc bảo mật thông tin cá nhân khách hàng. Trong trường hợp máy chủ lưu trữ thông tin bị hacker tấn công dẫn đến mất mát dữ liệu cá nhân khách hàng, Akishop sẽ có trách nhiệm thông báo vụ việc cho cơ quan chức năng điều tra xử lý kịp thời và thông báo cho khách hàng được biết.</li>
                <li>Bảo mật tuyệt đối mọi thông tin giao dịch trực tuyến của khách hàng bao gồm thông tin hóa đơn kế toán chứng từ số hóa tại trung tâm dữ liệu của Akishop.com.vn.</li>
                <li>Ban quản lý Akishop.com.vn yêu cầu các cá nhân khi mua hàng là khách hàng, tùy từng trường hợp phải cung cấp đầy đủ thông tin cá nhân có liên quan như: Họ và tên, địa chỉ liên lạc, email, số chứng minh nhân dân, điện thoại, số tài khoản, số thẻ thanh toán …., và chịu trách nhiệm về tính pháp lý của những thông tin trên. Ban quản lý Akishop.com.vn không chịu trách nhiệm cũng như không giải quyết mọi khiếu nại có liên quan đến quyền lợi của Khách hàng đó nếu xét thấy tất cả thông tin cá nhân của khách hàng đó cung cấp khi đăng ký ban đầu là không chính xác.</li>
            </ul>

            <h2>8. Quản lý thông tin xấu</h2>
            <h3>Quy định khách hàng</h3>
            <p>Khách hàng có trách nhiệm thông báo kịp thời cho website TMĐT Akishop.com.vn về những hành vi sử dụng trái phép, lạm dụng, vi phạm bảo mật để có biện pháp giải quyết phù hợp.</p>

            <h2>9. Trách nhiệm trong trường hợp phát sinh lỗi kỹ thuật</h2>
            <ul>
                <li>Website TMĐT Akishop.com.vn cam kết nỗ lực đảm bảo sự an toàn và ổn định của toàn bộ hệ thống kỹ thuật. Tuy nhiên, trong trường hợp xảy ra sự cố do lỗi của Akishop.com.vn, Akishop.com.vn sẽ ngay lập tức áp dụng các biện pháp để đảm bảo quyền lợi cho người mua hàng.</li>
                <li>Khi thực hiện các giao dịch trên Website, bắt buộc các khách hàng phải thực hiện đúng theo các quy trình hướng dẫn.</li>
                <li>Ban quản lý website TMĐT Akishop.com.vn cam kết cung cấp chất lượng dịch vụ tốt nhất cho các khách hàng tham gia giao dịch. Trường hợp phát sinh lỗi kỹ thuật, lỗi phần mềm hoặc các lỗi khách quan khác dẫn đến khách hàng không thể tham gia giao dịch được thì các khách hàng thông báo cho Ban quản lý website TMĐT qua địa chỉ email leduc2103@gmail.com hoặc qua điện thoại 0981523130 (từ 8:00 – 21:30 hằng ngày) chúng tôi sẽ khắc phục lỗi trong thời gian sớm nhất, tạo điều kiện cho các khách hàng tham gia website TMĐT Akishop.com.vn.</li>
                <li>Tuy nhiên, Ban quản lý website TMĐT Akishop.com.vn sẽ không chịu trách nhiệm giải quyết trong trường hợp thông báo của các khách hàng không đến được Ban quản lý, phát sinh từ lỗi kỹ thuật, lỗi đường truyền, phần mềm hoặc các lỗi khác không do Ban quản lý gây ra.</li>
            </ul>

            <h2>10. Quy trình tiếp nhận & giải quyết khiếu nại</h2>
            <p><strong>Bước 1:</strong> Người mua hàng có thể gửi khiếu nại của mình đến Akishop.com.vn qua các phương tiện sau:</p>
            <ul>
                <li>Email: leduc2103@gmail.com</li>
                <li>Số hotline: 0981523130</li>
                <li>Địa chỉ: 124 Lê Quang Định, phường Bình Thạnh, TP.HỒ CHÍ MINH</li>
            </ul>
            <p><strong>Bước 2:</strong> Akishop sẽ liên lạc với khách hàng để tìm hiểu nguyên nhân để thoả thuận đền bù (khi cần).</p>

            <h2>11. Quyền và nghĩa vụ của Ban quản lý website TMĐT Akishop.com.vn</h2>
            <h3>11.1. Quyền của Ban quản lý Akishop.com.vn:</h3>
            <ul>
                <li>Website TMĐT Akishop.com.vn sẽ tiến hành cung cấp các dịch vụ, sản phẩm cho khách hàng sau khi đã hoàn thành các thủ tục và các điều kiện bắt buộc đã nêu.</li>
                <li>Akishop.com.vn sẽ tiến hành xây dựng các chính sách dịch vụ trên trang web. Các chính sách này sẽ được công bố trên Akishop.com.vn.</li>
                <li>Trong trường hợp có cơ sở để chứng minh khách hàng cung cấp thông tin cho Website TMĐT Akishop.com.vn không chính xác, sai lệch, không đầy đủ hoặc có dấu hiệu vi phạm pháp luật hay thuần phong mỹ tục Việt Nam thì Website TMĐT Akishop.com.vn có quyền từ chối, tạm ngừng hoặc chấm dứt quyền sử dụng dịch vụ của khách hàng.</li>
                <li>Website TMĐT Akishop.com.vn có thể chấm dứt quyền khách hàng và quyền sử dụng một hoặc tất cả các dịch vụ của khách hàng trong trường hợp khách hàng vi phạm các Quy chế của Akishop.com.vn, hoặc có những hành vi ảnh hưởng đến hoạt động kinh doanh trên Website TMĐT Akishop.com.vn.</li>
                <li>Website TMĐT Akishop.com.vn có thể chấm dứt ngay quyền sử dụng dịch vụ khách hàng của khách hàng nếu Website TMĐT Akishop.com.vn phát hiện khách hàng đã phá sản, bị kết án hoặc đang trong thời gian thụ án, trong trường hợp khách hàng tiếp tục hoạt động có thể gây cho Website TMĐT Akishop.com.vn trách nhiệm pháp lý, có những hoạt động lừa đảo, giả mạo, gây rối loạn thị trường, gây mất đoàn kết đối với các khách hàng khác của Website TMĐT Akishop.com.vn, hoạt động vi phạm pháp luật hiện hành của Việt Nam.</li>
                <li>Trong trường hợp chấm dứt quyền sử dụng dịch vụ thì tất cả các chứng nhận, các quyền của khách hàng được cấp sẽ mặc nhiên hết giá trị và bị chấm dứt.</li>
                <li>Website TMĐT Akishop.com.vn giữ bản quyền sử dụng dịch vụ và các nội dung trên Website TMĐT Akishop.com.vn theo các quy định pháp luật về bảo hộ sở hữu trí tuệ tại Việt Nam. Tất cả các biểu tượng, nội dung theo các ngôn ngữ khác nhau đều thuộc quyền sở hữu của Website TMĐT Akishop.com.vn. Nghiêm cấm mọi hành vi sao chép, sử dụng và phổ biến bất hợp pháp các quyền sở hữu trên.</li>
                <li>Website TMĐT Akishop.com.vn giữ quyền được thay đổi bảng, biểu giá dịch vụ và phương thức thanh toán trong thời gian cung cấp dịch vụ cho khách hàng khách hàng theo nhu cầu và điều kiện khả năng của Website TMĐT Akishop.com.vn và sẽ báo trước cho khách hàng thời hạn là một (01) tháng.</li>
            </ul>

            <h3>11.2. Nghĩa vụ của Ban quản lý Akishop.com.vn</h3>
            <ul>
                <li>Website TMĐT Akishop.com.vn chịu trách nhiệm xây dựng dịch vụ bao gồm một số công việc chính như: nghiên cứu, thiết kế, mua sắm các thiết bị phần cứng và phần mềm, kết nối Internet, xây dựng chính sách phục vụ cho hoạt động Website TMĐT Akishop.com.vn trong điều kiện và phạm vi cho phép.</li>
                <li>Website TMĐT Akishop.com.vn sẽ tiến hành triển khai và hợp tác với các đối tác trong việc xây dựng hệ thống các dịch vụ, các công cụ tiện ích phục vụ cho việc giao dịch của các khách hàng tham gia và người sử dụng trên Website TMĐT Akishop.com.vn</li>
                <li>Website TMĐT Akishop.com.vn chịu trách nhiệm xây dựng, bổ sung hệ thống các kiến thức, thông tin về: nghiệp vụ ngoại thương, thương mại điện tử, hệ thống văn bản pháp luật thương mại trong nước và quốc tế, thị trường nước ngoài, cũng như các tin tức có liên quan đến hoạt động của Website TMĐT Akishop.com.vn.</li>
                <li>Website TMĐT Akishop.com.vn sẽ cố gắng đến mức cao nhất trong phạm vi và điều kiện có thể để duy trì hoạt động bình thường của Website TMĐT Akishop.com.vn và khắc phục các sự cố như: sự cố kỹ thuật về máy móc, lỗi phần mềm, hệ thống đường truyền internet, nhân sự, các biến động xã hội, thiên tai, mất điện, các quyết định của cơ quan nhà nước hay một tổ chức liên quan thứ ba. Tuy nhiên nếu những sự cố trên xảy ra nằm ngoài khả năng kiểm soát, là những trường hợp bất khả kháng mà gây thiệt hại cho khách hàng thì Website TMĐT Akishop.com.vn không phải chịu trách nhiệm liên đới.</li>
            </ul>

            <p><strong>Website TMĐT Akishop.com.vn có trách nhiệm:</strong></p>
            <ul>
                <li>Xây dựng và thực hiện cơ chế để đảm bảo việc đăng thông tin trên Website TMĐT Akishop.com.vn được thực hiện chính xác.</li>
                <li>Không đăng tải những thông tin bán hàng hóa, dịch vụ thuộc danh mục hàng hóa, dịch vụ cấm kinh doanh theo quy định của pháp luật và hàng hóa hạn chế kinh doanh theo quy định tại Thông tư 47/2014/TT-BCT.</li>
            </ul>

            <h2>12. Quyền và trách nhiệm khách hàng tham gia website TMĐT Akishop.com.vn</h2>
            <h3>12.1. Quyền của Khách hàng Website TMĐT Akishop.com.vn</h3>
            <p>Khách hàng có quyền đóng góp ý kiến cho Website TMĐT Akishop.com.vn trong quá trình hoạt động. Các kiến nghị được gửi trực tiếp bằng thư, fax hoặc email đến cho Website TMĐT Akishop.com.vn</p>

            <h3>12.2. Nghĩa vụ của Khách hàng sử dụng Website TMĐT Akishop.com.vn</h3>
            <ul>
                <li>Khách hàng sẽ tự chịu trách nhiệm về bảo mật và lưu giữ và mọi hoạt động sử dụng dịch vụ qua hộp thư điện tử của mình.</li>
                <li>Khách hàng cam kết không được thay đổi, chỉnh sửa, sao chép, truyền bá, phân phối, cung cấp và tạo những công cụ tương tự của dịch vụ do Website TMĐT Akishop.com.vn cung cấp cho một bên thứ ba nếu không được sự đồng ý của Website TMĐT Akishop.com.vn trong Quy định này.</li>
                <li>Khách hàng không được hành động gây mất uy tín của Website TMĐT Akishop.com.vn dưới mọi hình thức như gây mất đoàn kết giữa các khách hàng hoặc tuyên truyền, phổ biến những thông tin không có lợi cho uy tín của Website TMĐT Akishop.com.vn.</li>
            </ul>

            <h2>13. Những trường hợp từ chối hoặc hạn chế phục vụ khách hàng của Website TMĐT Akishop.com.vn (*)</h2>
            
            <table class="special-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Trường hợp đặc biệt</th>
                        <th>Hướng xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>
                            <strong>Các trường hợp từ chối phục vụ:</strong>
                            <ul>
                                <li>Khách hàng lợi dụng những chính sách bán hàng và chính sách Chăm sóc Khách hàng của Akishop.com.vn để trục lợi cá nhân và/ hoặc có những yêu cầu bất hợp lý Akishop.com.vn cho rằng không phù hợp hoặc không chính đáng.</li>
                                <li>Khách hàng có lời nói và/ hoặc hành động mang tính chất đe dọa, khiếm nhã, kích động bạo lực, không phù hợp với thuần phong mỹ tục, chống phá nhà nước hoặc vi phạm pháp luật.</li>
                                <li>Khách hàng có những yêu cầu và/ hoặc trao đổi thông tin nằm ngoài phạm vi Akishop.com.vn kinh doanh.</li>
                            </ul>
                        </td>
                        <td>Akishop.com.vn có quyền từ chối phục vụ Khách hàng tại các chuỗi cửa hàng của Akishop.com.vn, trên website và qua các hình thức giao dịch, tương tác khác (tổng đài, email, facebook,...)</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">2</td>
                        <td><strong>Trường hợp hạn chế phục vụ:</strong> khách hàng đặt sản phẩm đã sử dụng nhưng không có nhu cầu mua hàng.</td>
                        <td>
                            <ul>
                                <li>01 số điện thoại có lịch sử đặt 03 đơn hàng nhưng Khách hàng không đến siêu thị xem hàng, không phân biệt hình thức đặt hàng, không phân biệt hệ thống thì sẽ bị chặn đặt mua hàng Online trong 03 tháng.</li>
                                <li>Trường hợp KH đã bị chặn đặt hàng, KH muốn mua hàng thì đến trực tiếp cửa hàng mua theo hình thức offline.</li>
                                <li>Trường hợp, SĐT đặt hàng có 02 đơn hàng bị hủy tự động nhưng đặt lần thứ 03 có đến siêu thị xem hàng thì được reset lại</li>
                                <li>01 số điện thoại chỉ được đặt giữ 01 sản phẩm cùng loại, nếu muốn đặt giữ tiếp sản phẩm cùng loại khác phải ra siêu thị xem hàng hoặc chờ hệ thống hủy tự động của đơn hàng đã đặt. Sau đó, số điện thoại này mới được đặt giữ tiếp sản phẩm cùng loại.</li>
                                <li>01 số điện thoại chỉ được đặt giữ 03 sản phẩm cùng lúc nhưng không cùng loại.</li>
                                <li>Chặn đặt hàng trên website: Akishop.com.vn</li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2>14. Hiệu lực</h2>
            <p>Quy chế của Website TMĐT Akishop.com.vn chính thức có hiệu lực thi hành kể từ ngày ký Quyết định ban hành kèm theo Quy chế này, ngày 22/02/2024, Website TMĐT Akishop.com.vn có quyền và có thể thay đổi Quy chế này bằng cách thông báo lên Website TMĐT Akishop.com.vn cho các khách hàng biết. Quy chế sửa đổi có hiệu lực kể từ ngày Quyết định về việc sửa đổi Quy chế có hiệu lực. Việc khách hàng tiếp tục sử dụng dịch vụ sau khi Quy chế sửa đổi được công bố và thực thi đồng nghĩa với việc khách hàng đã chấp nhận Quy chế sửa đổi này.</p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="script.js"></script>
</body>
</html>
