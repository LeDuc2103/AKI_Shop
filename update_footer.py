import re
import os

# Danh sách các file cần cập nhật footer
files_to_update = [
    'cart.php',
    'login.php',
    'register.php',
    'shop.php',
    'about.php',
    'contact.php',
    'blog.php',
    'sproduct.php',
    'invoice.php',
    'payment_cod.php'
]

base_path = r'd:\wamp\www\KLTN'

for filename in files_to_update:
    filepath = os.path.join(base_path, filename)
    
    if not os.path.exists(filepath):
        print(f"❌ Không tìm thấy: {filename}")
        continue
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Tìm và thay thế footer
        # Pattern: từ <footer đến </footer>
        pattern = r'<footer[^>]*>.*?</footer>'
        replacement = "<?php include 'includes/footer.php'; ?>"
        
        new_content = re.sub(pattern, replacement, content, flags=re.DOTALL)
        
        if new_content != content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"✅ Đã cập nhật: {filename}")
        else:
            print(f"⚠️ Không tìm thấy footer trong: {filename}")
            
    except Exception as e:
        print(f"❌ Lỗi khi xử lý {filename}: {str(e)}")

print("\n✅ Hoàn tất cập nhật footer cho tất cả các trang!")
