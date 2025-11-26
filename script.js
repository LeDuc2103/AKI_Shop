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
