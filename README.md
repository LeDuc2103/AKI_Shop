# 🛍️ AKI-SHOP - E-Reader & Smart Devices Store

## 📖 Giới thiệu

AKI-SHOP là website thương mại điện tử chuyên cung cấp máy đọc sách điện tử, thiết bị thông minh và phụ kiện chính hãng. Website được xây dựng theo mô hình MVC (Model-View-Controller) để đảm bảo code dễ bảo trì và mở rộng.

## 🏗️ Cấu trúc thư mục (MVC Architecture)

```
AKI-SHOP/
├── 📁 assets/                    # Tài nguyên tĩnh
│   ├── 📁 css/                   # Stylesheet files
│   │   ├── style.css             # CSS chính
│   │   ├── accessories-styles.css # CSS cho section phụ kiện
│   │   └── news-styles.css       # CSS cho section tin tức
│   ├── 📁 js/                    # JavaScript utilities
│   │   └── scripts.js            # UI components và animations
│   └── 📁 images/                # Hình ảnh
│       ├── 📁 logo/              # Logo và branding
│       ├── 📁 banner/            # Banner và slides
│       ├── 📁 product/           # Hình ảnh sản phẩm
│       └── 📁 blog/              # Hình ảnh tin tức
├── 📁 src/                       # Source code chính
│   ├── 📁 models/                # Model Layer - Quản lý dữ liệu
│   │   ├── ProductModel.js       # Model cho sản phẩm
│   │   └── NewsModel.js          # Model cho tin tức
│   ├── 📁 controllers/           # Controller Layer - Logic điều khiển
│   │   └── HomeController.js     # Controller cho trang chủ
│   ├── 📁 views/                 # View Layer - Giao diện người dùng
│   │   └── index.html            # Template trang chủ
│   └── app.js                    # Entry point chính của ứng dụng
├── index.html                    # File HTML chính (root)
├── package.json                  # NPM configuration
├── .gitignore                    # Git ignore rules
└── README.md                     # Tài liệu này
```

## 🎯 Mô hình MVC

### **Model Layer** (`src/models/`)
- **ProductModel.js**: Quản lý dữ liệu sản phẩm, phụ kiện, categories, brands
- **NewsModel.js**: Quản lý dữ liệu tin tức, bài viết, search

### **View Layer** (`src/views/`)
- **index.html**: Template HTML cho trang chủ
- Chứa cấu trúc HTML thuần, không chứa logic
- Hiển thị dữ liệu được truyền từ Controller

### **Controller Layer** (`src/controllers/`)
- **HomeController.js**: Điều khiển tương tác giữa Model và View
- Xử lý user events, business logic, data binding
- Quản lý state và navigation

## 🚀 Tính năng chính

### ✨ Giao diện người dùng
- 🎨 Responsive design cho mọi thiết bị
- 🎪 Carousel cho sản phẩm hot sale
- 📰 Section tin tức với navigation
- 🛒 Giao diện shopping cart
- 🔍 Tìm kiếm sản phẩm

### 🛠️ Tính năng kỹ thuật
- 📱 Mobile-first responsive design
- ⚡ Lazy loading cho hình ảnh
- 🎯 SEO optimized
- 🔄 Smooth animations và transitions
- 📊 Performance monitoring
- 🛡️ Error handling

## 🎨 Design System

### 🎨 Màu sắc chính
- **Primary Green**: `#2ecc71` - Màu chủ đạo
- **Light Green**: `#8bc34a` - Màu phụ (tin tức)
- **Red**: `#d32f2f` - Buttons và accents
- **Dark**: `#333` - Text màu đen
- **Gray**: `#666` - Text phụ

### 📱 Breakpoints
- **Mobile**: `< 768px`
- **Tablet**: `768px - 1024px`
- **Desktop**: `> 1024px`

## 🚀 Cách chạy dự án

### 1. Clone repository
```bash
git clone https://github.com/LeDuc2103/AKI_Shop.git
cd AKI_Shop
```

### 2. Chạy với Live Server (khuyến nghị)
- Cài đặt Live Server extension trong VS Code
- Right-click vào `index.html` → "Open with Live Server"

### 3. Hoặc chạy với HTTP server
```bash
# Python 3
python -m http.server 8000

# Node.js
npx http-server

# PHP
php -S localhost:8000
```

### 4. Truy cập website
Mở trình duyệt và truy cập: `http://localhost:8000`

## 📱 Sections chính

### 🏠 Header & Navigation
- Logo và branding
- Thanh tìm kiếm với categories
- Navigation menu
- Giỏ hàng và user account

### 🔥 Hot Sale Products
- Carousel sản phẩm khuyến mãi
- Hiển thị giá gốc và giá sale
- Rating 5 sao
- Responsive navigation

### 🛡️ Brand Showcases
- Boox products showcase
- Kindle products showcase  
- Kobo products showcase
- reMarkable products showcase

### 🔧 Accessories Section
- 12 sản phẩm phụ kiện
- Grid layout responsive (6→4→3→2→1)
- Hover effects

### 📰 News Section
- 3 bài viết tin tức mới nhất
- Carousel navigation
- Meta data (ngày, lượt xem)

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Icons**: Font Awesome 6.2.0
- **Architecture**: MVP Pattern
- **Responsive**: CSS Grid, Flexbox
- **Performance**: Lazy Loading, Debouncing
- **Version Control**: Git, GitHub

## 🔧 Development Guidelines

### 📝 Code Style
- Sử dụng ES6+ features
- Comment code bằng tiếng Việt có dấu
- Tên biến và function bằng tiếng Anh
- Indentation: 4 spaces

### 🗂️ File Organization
- CSS: Mỗi section có file riêng
- JS: Tách Models, Controllers theo chức năng
- Images: Phân loại theo thư mục

## 💡 Lợi ích của MVC Architecture

### ✨ **Separation of Concerns** - Tách biệt trách nhiệm
- Model: Chỉ quản lý dữ liệu và business logic
- View: Chỉ hiển thị giao diện người dùng  
- Controller: Chỉ xử lý user input và điều phối

### 🔄 **Maintainability** - Dễ bảo trì
- Thay đổi giao diện không ảnh hưởng logic
- Thay đổi data structure không ảnh hưởng UI
- Debug dễ dàng vì logic được tách biệt rõ ràng

### 📈 **Scalability** - Mở rộng linh hoạt
- Thêm Views mới mà không thay đổi Models
- Thêm Controllers mới cho các trang khác
- Reuse Models cho nhiều Controllers khác nhau

### 🧪 **Testability** - Dễ kiểm thử
- Test Models độc lập không cần UI
- Test Controllers với mock Models
- Test Views với static data

### 👥 **Team Development** - Phát triển nhóm hiệu quả
- Frontend dev làm Views
- Backend dev làm Models và APIs
- Full-stack dev làm Controllers

### 🔄 Git Workflow
```bash
# Tạo branch mới
git checkout -b feature/ten-tinh-nang

# Commit changes
git add .
git commit -m "feat: thêm tính năng mới"

# Push to GitHub
git push origin feature/ten-tinh-nang
```

## 📈 Performance Optimization

- ⚡ Image lazy loading
- 🗜️ CSS minification (production)
- 📦 JS code splitting
- 🚀 CDN cho Font Awesome
- 📊 Performance monitoring

## 🐛 Debugging

### Console Logs
- ✅ Application startup messages
- 📊 Performance metrics
- 🚨 Error handling
- 🔍 Search functionality logs

### Browser DevTools
- Sử dụng DevTools để debug responsive
- Network tab để kiểm tra loading times
- Console để xem logs và errors

## 🤝 Contributing

1. Fork repository
2. Tạo branch mới (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Tạo Pull Request

## 📧 Contact

- **GitHub**: [@LeDuc2103](https://github.com/LeDuc2103)
- **Repository**: [AKI_Shop](https://github.com/LeDuc2103/AKI_Shop)

## 📜 License

Dự án này được phân phối dưới MIT License. Xem file `LICENSE` để biết thêm chi tiết.

---

⭐ **Nếu bạn thấy dự án này hữu ích, hãy cho một star nhé!** ⭐
