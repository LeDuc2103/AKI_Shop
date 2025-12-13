<?php 
    session_start();
    
    /*
    File order.php
    Đây là file chính. Dùng để tạo đơn hàng và hiển thị giao diện cổng thanh toán (Checkout)
    
    URL tạo đơn hàng:           https://yourwebsite.tld/order.php
    URL giao diện thanh toán:   https://yourwebsite.tld/order.php?id={order_id}
    
    */
    // Include file db_connect.php, file chứa toàn bộ kết nối CSDL
    require('../config/database.php');
    
    // Khởi tạo database
    $db = new Database();
    $conn = $db->getConnection();
   
   // Khởi tạo biến $order_id
    if(isset($_GET["ma_donhang"]) && is_numeric($_GET["ma_donhang"]))
        $order_id = $_GET["ma_donhang"];
    else 
        $order_id = '';
        
    // Nếu method là POST thì tạo đơn hàng
    if (isset($_POST) && isset($_POST["tong_tien"])) {
        
        try {
            $order_total = $_POST["tong_tien"];
            $ten_nguoinhan = $_POST["ten_nguoinhan"];
            $diachi_nhan = $_POST["diachi_nhan"];
            $so_dienthoai = $_POST["so_dienthoai"];
            $email_nguoinhan = $_POST["email_nguoinhan"];
            $ma_user = $_POST["ma_user"];
            
            if(is_numeric($order_total)) {
                
                // Tạo mã đơn hàng
                $sql = "INSERT INTO don_hang (
                    ma_user,
                    ten_nguoinhan, 
                    diachi_nhan, 
                    so_dienthoai, 
                    email_nguoinhan,
                    tong_tien,
                    phuongthuc_thanhtoan,
                    trangthai_thanhtoan,
                    trang_thai,
                    created_at
                ) VALUES (
                    :ma_user,
                    :ten_nguoinhan,
                    :diachi_nhan,
                    :so_dienthoai,
                    :email_nguoinhan,
                    :tong_tien,
                    'SePay',
                    'chua_thanh_toan',
                    'cho_xac_nhan',
                    NOW()
                )";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':ma_user', $ma_user);
                $stmt->bindParam(':ten_nguoinhan', $ten_nguoinhan);
                $stmt->bindParam(':diachi_nhan', $diachi_nhan);
                $stmt->bindParam(':so_dienthoai', $so_dienthoai);
                $stmt->bindParam(':email_nguoinhan', $email_nguoinhan);
                $stmt->bindParam(':tong_tien', $order_total);
                
                if ($stmt->execute()) {
                    // Lấy ID của đơn hàng vừa insert
                    $order_id = $conn->lastInsertId();
                    
                    // Lấy sản phẩm từ giỏ hàng và lưu vào chitiet_donhang
                    $cartStmt = $conn->prepare("
                        SELECT gh.id_sanpham, gh.so_luong, sp.gia
                        FROM gio_hang gh
                        INNER JOIN san_pham sp ON gh.id_sanpham = sp.id_sanpham
                        WHERE gh.ma_user = ?
                    ");
                    $cartStmt->execute([$ma_user]);
                    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Lưu từng sản phẩm vào chitiet_donhang
                    $detailSql = "INSERT INTO chitiet_donhang (ma_donhang, id_sanpham, so_luong, don_gia) VALUES (?, ?, ?, ?)";
                    $detailStmt = $conn->prepare($detailSql);
                    
                    foreach ($cartItems as $item) {
                        $detailStmt->execute([
                            $order_id,
                            $item['id_sanpham'],
                            $item['so_luong'],
                            $item['gia']
                        ]);
                        
                        // Giảm số lượng sản phẩm trong kho
                        $updateQty = $conn->prepare("UPDATE san_pham SET so_luong = so_luong - ? WHERE id_sanpham = ?");
                        $updateQty->execute([$item['so_luong'], $item['id_sanpham']]);
                    }
                    
                    // KHÔNG xóa giỏ hàng ngay - chỉ xóa khi thanh toán thành công
                    // Giỏ hàng sẽ được xóa trong sepay_webhook.php khi nhận được xác nhận thanh toán
                    
                    // Redirect đến trang checkout để hiển thị giao diện thanh toán
                    header("Location: order.php?ma_donhang=" . $order_id);
                    exit();
                } else {
                    echo json_encode(['success'=>FALSE, 'message' => 'Cannot insert order']);
                }
            }
        } catch(PDOException $e) {
            echo json_encode(['success'=>FALSE, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } 
   
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SePay Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/responsive.css?v=1765636816">
  </head>
  <body>
    <div class="row my-5 px-2">
        <div class="col-md-8 mx-auto">
            
            <!-- Form tạo đơn hàng. Mặc định hiển thị form này khi vào https://123host.asia/sepay/order.php -->
            <?php if(!is_numeric($order_id)) { ?>
            <h1>Đặt hàng</h1>
            <form  method="POST" action="">
                  
              <div class="mb-3">
                <label for="amountInput" class="form-label">Số tiền</label>
                <input type="number" name="total" class="form-control" id="amountInput" aria-describedby="emailHelp" value="3000">
                <div id="amountInputHelp" class="form-text">Điền số tiền</div>
              </div>
               
              <button type="submit" class="btn btn-primary">Đặt hàng</button>
            </form>
            
            <?php } else { ?>
            <!-- Form tạo đơn hàng -->
            
            <!-- Hiển thị Giao diện thanh toán (Checkout) khi tạo đơn hàng thành công-->
            <?php 
                // Lấy thông tin đơn hàng
                $stmt = $conn->prepare("SELECT * FROM don_hang WHERE ma_donhang = ?");
                $stmt->execute([$order_id]);
                $order_details = $stmt->fetch(PDO::FETCH_OBJ);
                
                if(!$order_details) {
                    die('Không tìm thấy đơn hàng');
                }
                
                // Thông tin ngân hàng SePay
                $bank_info = [
                    'bank_name' => 'MBBank',
                    'account_number' => '0981523130',
                    'account_name' => 'LE VAN TUC',
                    'bank_logo' => 'https://qr.sepay.vn/assets/img/banklogo/MB.png'
                ];
                
                // Tạo nội dung chuyển khoản
                $payment_content = 'DH' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
                
                // URL QR Code
                $qr_url = sprintf(
                    'https://qr.sepay.vn/img?bank=%s&acc=%s&template=compact&amount=%d&des=%s',
                    $bank_info['bank_name'],
                    $bank_info['account_number'],
                    intval($order_details->tong_tien),
                    $payment_content
                );
                
                // Kiểm tra giao dịch trong tb_transactions
                $trans_stmt = $conn->prepare("
                    SELECT * FROM tb_transactions 
                    WHERE transaction_content LIKE ? 
                    ORDER BY id DESC LIMIT 1
                ");
                $trans_stmt->execute(['%' . $payment_content . '%']);
                $transaction = $trans_stmt->fetch(PDO::FETCH_OBJ);
            ?>
            
           
<div class="row">
    <div class="col-md-8">
         <h1><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle text-success" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
  <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
</svg> Đặt hàng</h1>
<span class="text-muted">Mã đơn hàng #<?= $payment_content; ?></span>

<!-- Thông tin đơn hàng -->
<div class="alert alert-info mt-3">
    <strong>📦 Thông tin đơn hàng:</strong><br>
    Người nhận: <strong><?= htmlspecialchars($order_details->ten_nguoinhan); ?></strong><br>
    Địa chỉ: <?= htmlspecialchars($order_details->diachi_nhan); ?><br>
    SĐT: <?= htmlspecialchars($order_details->so_dienthoai); ?><br>
    Tổng tiền: <strong class="text-danger"><?= number_format($order_details->tong_tien); ?> VNĐ</strong>
</div>

<?php if($transaction): ?>
<!-- Thông tin giao dịch đã có -->
<div class="alert alert-success mt-3">
    <strong>✅ Giao dịch đã được ghi nhận:</strong><br>
    Ngân hàng: <?= htmlspecialchars($transaction->gateway); ?><br>
    Số tiền: <strong><?= number_format($transaction->amount_in); ?> VNĐ</strong><br>
    Thời gian: <?= $transaction->transaction_date; ?><br>
    Nội dung: <?= htmlspecialchars($transaction->transaction_content); ?>
</div>
<?php endif; ?>

<div id="success_pay_box" class="p-4 text-center pt-4 border border-success border-3 mt-5 bg-light" style="display:<?= ($order_details->trangthai_thanhtoan === 'da_thanh_toan') ? 'block' : 'none'; ?>; border-radius: 15px;">
    <div class="mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
    </div>
    <h2 class="text-success fw-bold mb-3">🎉 Thanh toán thành công!</h2>
    <p class="text-center text-dark mb-4" style="font-size: 1.1em;">
        Chúng tôi đã nhận được thanh toán của bạn.<br>
        Đơn hàng <strong><?= $payment_content; ?></strong> sẽ được xử lý và giao đến bạn trong thời gian sớm nhất!
    </p>
    <div class="mt-4">
        <a href="../my_orders.php" class="btn btn-success btn-lg me-2">
            <i class="bi bi-box-seam"></i> Xem đơn hàng của tôi
        </a>
        <a href="../shop.php" class="btn btn-outline-success btn-lg">
            <i class="bi bi-cart"></i> Tiếp tục mua sắm
        </a>
    </div>
</div>
<div class="row mt-5 px-2" id="checkout_box" style="display:<?= ($order_details->trangthai_thanhtoan === 'da_thanh_toan') ? 'none' : 'flex'; ?>;">
    <div class="col-12 text-center my-2 border"><p class="mt-2">Hướng dẫn thanh toán qua chuyển khoản ngân hàng</p></div>
    <div class="col-md-6 border text-center p-2">
        <p class="fw-bold">Cách 1: Mở app ngân hàng và quét mã QR</p>
        <div class="my-2">
            <img src="<?= $qr_url; ?>" class="img-fluid" alt="QR Code Thanh toán" style="max-width: 300px;">
            <div class="mt-3">
                <span class="badge bg-warning text-dark" id="payment-status">
                    <i class="bi bi-clock"></i> Đang chờ thanh toán...
                    <div class="spinner-border spinner-border-sm ms-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 border p-2">
        <p class="fw-bold">Cách 2: Chuyển khoản thủ công theo thông tin</p>
        <div class="text-center">
            <img src="<?= $bank_info['bank_logo']; ?>" class="img-fluid" style="max-height:50px" alt="<?= $bank_info['bank_name']; ?>">
            <p class="fw-bold mt-2">Ngân hàng <?= $bank_info['bank_name']; ?></p>
        </div>
        
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td width="40%"><i class="bi bi-person-circle"></i> Chủ tài khoản:</td>
                    <td><strong><?= $bank_info['account_name']; ?></strong></td>
                </tr>
                <tr>
                    <td><i class="bi bi-credit-card"></i> Số tài khoản:</td>
                    <td>
                        <strong><?= $bank_info['account_number']; ?></strong>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= $bank_info['account_number']; ?>')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </td>
                </tr>
                <tr>
                    <td><i class="bi bi-cash"></i> Số tiền:</td>
                    <td>
                        <strong class="text-danger"><?= number_format($order_details->tong_tien); ?> VNĐ</strong>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= $order_details->tong_tien; ?>')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </td>
                </tr>
                <tr>
                    <td><i class="bi bi-chat-text"></i> Nội dung CK:</td>
                    <td>
                        <strong class="text-primary"><?= $payment_content; ?></strong>
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= $payment_content; ?>')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> 
            <strong>Lưu ý:</strong> Vui lòng giữ nguyên nội dung <strong><?= $payment_content; ?></strong> 
            để hệ thống tự động xác nhận thanh toán trong vài giây.
        </div>
    </div>
</div>
    </div>
    <div class="col-md-4 bg-light border-top">
        <p class="fw-bold"><i class="bi bi-receipt"></i> Thông tin thanh toán</p>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>Mã đơn hàng:</td>
                    <td class="text-end"><strong><?= $payment_content; ?></strong></td>
                </tr>
                <tr>
                    <td>Người nhận:</td>
                    <td class="text-end"><?= htmlspecialchars($order_details->ten_nguoinhan); ?></td>
                </tr>
                <tr>
                    <td>Số điện thoại:</td>
                    <td class="text-end"><?= htmlspecialchars($order_details->so_dienthoai); ?></td>
                </tr>
                <tr>
                    <td>Phương thức:</td>
                    <td class="text-end"><span class="badge bg-primary"><?= $order_details->phuongthuc_thanhtoan; ?></span></td>
                </tr>
                <tr>
                    <td>Trạng thái:</td>
                    <td class="text-end">
                        <?php if($order_details->trangthai_thanhtoan === 'da_thanh_toan'): ?>
                            <span class="badge bg-success">Đã thanh toán</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Chưa thanh toán</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="table-active">
                    <td><strong>Tổng cộng:</strong></td>
                    <td class="text-end"><strong class="text-danger fs-5"><?= number_format($order_details->tong_tien); ?> VNĐ</strong></td>
                </tr>
            </tbody>
        </table>
        
        <?php if($transaction): ?>
        <div class="alert alert-info">
            <strong><i class="bi bi-info-circle"></i> Giao dịch cuối:</strong><br>
            <small>
                Thời gian: <?= $transaction->transaction_date; ?><br>
                Số tiền: <?= number_format($transaction->amount_in); ?> VNĐ
            </small>
        </div>
        <?php endif; ?>
        
    </div>
</div>
<div>
    <p class="mt-5"><a class="text-decoration-none" href=" https://nodose-jamika-astylar.ngrok-free.dev/order.php"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
</svg><a href="../cart.php"> Quay lại</a></p>
</div>


            <!-- Hiển thị Giao diện thanh toán (Checkout) khi tạo đơn hàng thành công-->

            <?php } ?>

            

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
      <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="../js/mobile-responsive.js?v=1765636816"></script>
      
      
      <?php
        // Nếu đang ở giao diện checkout
      if(isset($order_id)) {?>
      <script>
      // Function copy to clipboard
      function copyToClipboard(text) {
          navigator.clipboard.writeText(text).then(function() {
              alert('Đã copy: ' + text);
          }).catch(function(err) {
              console.error('Copy failed:', err);
          });
      }
      
      // Khởi tạo trạng thái thanh toán từ PHP
      var pay_status = '<?= ($order_details->trangthai_thanhtoan === "da_thanh_toan") ? "Paid" : "Unpaid"; ?>';
      console.log('💳 Initial payment status:', pay_status);
      
      // Hàm kiểm tra trạng thái đơn hàng
      // Sử dụng Ajax để lấy trạng thái đơn hàng. Nếu thanh toán thành công thì hiển thị Box đã thanh toán thành công, ẩn box checkout
      function check_payment_status() {
          if(pay_status == 'Unpaid') {
               $.ajax({
                    type: "POST",
                    data: {ma_donhang: <?= $order_id;?>},
                    url: "check_payment_status.php",
                    dataType:"json",
                    success: function(data){
                        console.log('⏳ Checking payment status:', data);
                        if(data.paid === true || data.payment_status === "da_thanh_toan") {
                            $("#checkout_box").hide();
                            $("#success_pay_box").show();
                            pay_status = 'Paid';
                            
                            // Dừng việc check status
                            console.log('✅ Payment confirmed!');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ AJAX Error:', status, error);
                        console.error('Response:', xhr.responseText);
                    }
                  });
              }
          }
        //Kiểm tra trạng thái đơn hàng 1 giây một lần để phản hồi nhanh hơn
        setInterval(check_payment_status, 1000);
      </script>
      <?php } ?>

  </body>
</html>