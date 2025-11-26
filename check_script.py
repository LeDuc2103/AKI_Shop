import os
import re

# Danh sách các file đã cập nhật
files = [
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

for filename in files:
    filepath = os.path.join(base_path, filename)
    
    if not os.path.exists(filepath):
        continue
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Kiểm tra xem đã có script.js chưa
        if 'script.js' not in content:
            # Thêm script.js trước </body>
            content = content.replace('</body>', '    <script src="script.js"></script>\n</body>')
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✅ Đã thêm script.js vào: {filename}")
        else:
            print(f"✓ {filename} đã có script.js")
            
    except Exception as e:
        print(f"❌ Lỗi: {filename}: {str(e)}")

print("\n✅ Hoàn tất kiểm tra!")
