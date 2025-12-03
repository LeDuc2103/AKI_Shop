// Search Box Toggle - Định nghĩa đầu tiên để có thể dùng inline onclick
function toggleSearch(event) {
    event.preventDefault();
    const searchBox = document.getElementById('search-box');
    const searchInput = document.getElementById('search-input');
    
    if (searchBox.classList.contains('active')) {
        searchBox.classList.remove('active');
    } else {
        searchBox.classList.add('active');
        // Focus vào input sau khi mở
        setTimeout(() => {
            searchInput.focus();
        }, 300);
    }
}

// Thêm sự kiện hover cho search icon
document.addEventListener('DOMContentLoaded', function() {
    const searchIcon = document.getElementById('search-icon');
    const searchBox = document.getElementById('search-box');
    const searchInput = document.getElementById('search-input');
    
    if (searchIcon && searchBox) {
        // Hiển thị search box khi hover vào icon
        searchIcon.addEventListener('mouseenter', function() {
            searchBox.classList.add('active');
            setTimeout(() => {
                searchInput.focus();
            }, 300);
        });
        
        // Đóng search box khi rê chuột ra khỏi search box
        searchBox.addEventListener('mouseleave', function(event) {
            // Kiểm tra xem chuột có đang ở trong search icon không
            if (!searchIcon.contains(event.relatedTarget)) {
                searchBox.classList.remove('active');
            }
        });
        
        // Giữ search box mở khi di chuyển chuột vào
        searchBox.addEventListener('mouseenter', function() {
            searchBox.classList.add('active');
        });
    }
});

const bar = document.getElementById('bar');
const nav = document.getElementById('navbar');
const close = document.getElementById('close');

// Mở menu mobile
if(bar){
    bar.addEventListener('click', (e) => {
        e.preventDefault();
        nav.classList.add('active');
        console.log('Menu opened'); // Debug
    });
}

// Đóng menu mobile
if(close){
    close.addEventListener('click', (e) => {
        e.preventDefault();
        nav.classList.remove('active');
        console.log('Menu closed'); // Debug
    });
}

// Đóng menu khi click vào overlay (ngoài menu)
document.addEventListener('click', (e) => {
    if(nav.classList.contains('active') && !nav.contains(e.target) && !bar.contains(e.target)){
        nav.classList.remove('active');
    }
});

// Đóng menu khi click vào link menu (trên mobile)
if(window.innerWidth <= 799){
    const navLinks = document.querySelectorAll('#navbar li a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            nav.classList.remove('active');
        });
    });
}

// User dropdown functionality - DISABLED (Using pure CSS version)
// CSS-only solution is more reliable and doesn't require JavaScript

console.log('User dropdown: Using pure CSS version - no JavaScript needed');

// Add to Cart functionality
function addToCart(productId, productName, price) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_to_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    showNotification('Đã thêm "' + productName + '" vào giỏ hàng!', 'success');
                    updateCartCount();
                } else {
                    showNotification(response.message || 'Có lỗi xảy ra!', 'error');
                }
            } catch (e) {
                showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng!', 'error');
            }
        }
    };
    
    xhr.send('product_id=' + productId + '&quantity=1&price=' + price);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'cart-notification ' + type;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function updateCartCount() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_cart_count.php', true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge && response.count) {
                    cartBadge.textContent = response.count;
                }
            } catch (e) {
                console.error('Error updating cart count');
            }
        }
    };
    
    xhr.send();
}

// Đóng search box khi nhấn ESC
document.addEventListener('keydown', function(event) {
    const searchBox = document.getElementById('search-box');
    
    if (event.key === 'Escape' && searchBox && searchBox.classList.contains('active')) {
        searchBox.classList.remove('active');
    }
});

// Search Suggestions
let searchTimeout;
const searchInput = document.getElementById('search-input');
const suggestionsBox = document.getElementById('search-suggestions');

if (searchInput && suggestionsBox) {
    // Lắng nghe sự kiện nhập liệu
    searchInput.addEventListener('input', function() {
        const keyword = this.value.trim();
        
        // Xóa timeout trước đó
        clearTimeout(searchTimeout);
        
        // Nếu từ khóa rỗng hoặc quá ngắn, ẩn suggestions
        if (keyword.length < 2) {
            suggestionsBox.classList.remove('active');
            suggestionsBox.innerHTML = '';
            return;
        }
        
        // Debounce: chờ 300ms sau khi user ngừng gõ
        searchTimeout = setTimeout(() => {
            fetchSuggestions(keyword);
        }, 300);
    });
    
    // Đóng suggestions khi click outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.search-input-wrapper')) {
            suggestionsBox.classList.remove('active');
        }
    });
}

function fetchSuggestions(keyword) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/search_suggestions.php?keyword=' + encodeURIComponent(keyword), true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                displaySuggestions(response.suggestions);
            } catch (e) {
                console.error('Error parsing suggestions:', e);
            }
        }
    };
    
    xhr.onerror = function() {
        console.error('Error fetching suggestions');
    };
    
    xhr.send();
}

function displaySuggestions(suggestions) {
    const suggestionsBox = document.getElementById('search-suggestions');
    
    if (!suggestions || suggestions.length === 0) {
        suggestionsBox.innerHTML = '<div class="no-suggestions">Không tìm thấy sản phẩm phù hợp</div>';
        suggestionsBox.classList.add('active');
        return;
    }
    
    let html = '';
    suggestions.forEach(item => {
        const price = item.sale_price && item.sale_price > 0 
            ? `<del>${formatPrice(item.price)}</del> ${formatPrice(item.sale_price)}`
            : formatPrice(item.price);
        
        const image = item.image || 'img/products/f1.jpg';
        
        html += `
            <a href="sproduct.php?id=${item.id}" class="suggestion-item">
                <img src="${image}" alt="${escapeHtml(item.name)}" class="suggestion-image" onerror="this.src='img/products/f1.jpg'">
                <div class="suggestion-details">
                    <p class="suggestion-name">${escapeHtml(item.name)}</p>
                    <p class="suggestion-category">${escapeHtml(item.category)}</p>
                </div>
                <div class="suggestion-price">${price}</div>
            </a>
        `;
    });
    
    suggestionsBox.innerHTML = html;
    suggestionsBox.classList.add('active');
}

function formatPrice(price) {
    if (!price) return '0 VNĐ';
    return new Intl.NumberFormat('vi-VN').format(price) + ' VNĐ';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== SCROLL TO TOP BUTTON =====
(function() {
    // Khởi tạo scroll to top
    function initScrollToTop() {
        let scrollToTopBtn = document.getElementById('scrollToTop');
        
        // Nếu không tìm thấy nút trong HTML, tạo mới
        if (!scrollToTopBtn) {
            scrollToTopBtn = document.createElement('button');
            scrollToTopBtn.id = 'scrollToTop';
            scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            scrollToTopBtn.title = 'Trở về đầu trang';
            document.body.appendChild(scrollToTopBtn);
        }
        
        // Hàm kiểm tra và hiển thị/ẩn nút
        function toggleScrollButton() {
            if (window.pageYOffset > 300 || document.documentElement.scrollTop > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        }
        
        // Lắng nghe sự kiện scroll
        window.addEventListener('scroll', toggleScrollButton);
        
        // Xử lý click
        scrollToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Kiểm tra ngay khi load
        toggleScrollButton();
    }
    
    // Chạy khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollToTop);
    } else {
        initScrollToTop();
    }
})();
