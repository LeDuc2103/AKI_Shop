<?php
// File test để kiểm tra đường dẫn upload
session_start();

// Giả lập admin đã login (để test)
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_email'] = 'test@admin.com';

echo "=== TEST UPLOAD PATH ===<br><br>";

// Test 1: Kiểm tra session
echo "<strong>1. Session check:</strong><br>";
echo "admin_logged_in: " . (isset($_SESSION['admin_logged_in']) ? 'YES' : 'NO') . "<br><br>";

// Test 2: Kiểm tra đường dẫn file upload
echo "<strong>2. Upload image path test:</strong><br>";
$upload_script_path = __DIR__ . '/nhanvien/upload_image.php';
echo "Upload script path: " . $upload_script_path . "<br>";
echo "File exists: " . (file_exists($upload_script_path) ? 'YES' : 'NO') . "<br><br>";

// Test 3: Kiểm tra thư mục lưu ảnh
echo "<strong>3. Image directory test:</strong><br>";
$img_dir = __DIR__ . '/img/blog/';
echo "Image directory: " . $img_dir . "<br>";
echo "Directory exists: " . (is_dir($img_dir) ? 'YES' : 'NO') . "<br>";
echo "Directory writable: " . (is_writable($img_dir) ? 'YES' : 'NO') . "<br><br>";

// Test 4: Test AJAX URL
echo "<strong>4. AJAX URL test:</strong><br>";
echo "From admin.php, the AJAX URL should be: <code>nhanvien/upload_image.php</code><br>";
echo "Full server path would be: " . $_SERVER['DOCUMENT_ROOT'] . "/KLTN_AKISTORE/nhanvien/upload_image.php<br><br>";

// Test 5: Tạo form upload test
?>
<strong>5. Upload test form:</strong><br>
<form id="testUploadForm" enctype="multipart/form-data">
    <input type="file" id="testFile" accept="image/*"><br><br>
    <button type="button" onclick="testUpload()">Test Upload</button>
</form>
<div id="result" style="margin-top: 20px; padding: 10px; background: #f0f0f0;"></div>

<script>
function testUpload() {
    var fileInput = document.getElementById('testFile');
    var resultDiv = document.getElementById('result');
    
    if (!fileInput.files.length) {
        resultDiv.innerHTML = '<span style="color: red;">Vui lòng chọn file!</span>';
        return;
    }
    
    var formData = new FormData();
    formData.append('file', fileInput.files[0]);
    
    resultDiv.innerHTML = 'Đang upload...';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'nhanvien/upload_image.php', true);
    
    xhr.onload = function() {
        console.log('Status:', xhr.status);
        console.log('Response:', xhr.responseText);
        
        if (xhr.status === 200) {
            try {
                var json = JSON.parse(xhr.responseText);
                resultDiv.innerHTML = '<span style="color: green;">✓ Upload thành công!</span><br>Location: ' + json.location;
            } catch(e) {
                resultDiv.innerHTML = '<span style="color: orange;">Server response:</span><br>' + xhr.responseText;
            }
        } else {
            resultDiv.innerHTML = '<span style="color: red;">✗ Lỗi HTTP ' + xhr.status + '</span><br>' + xhr.responseText;
        }
    };
    
    xhr.onerror = function() {
        resultDiv.innerHTML = '<span style="color: red;">✗ Lỗi kết nối</span>';
    };
    
    xhr.send(formData);
}
</script>

<hr>
<p><a href="admin.php?action=sanpham&act=them">← Quay lại trang thêm sản phẩm</a></p>
