/**
 * Main Application Entry Point
 * Khởi tạo ứng dụng theo mô hình MVC
 */

// Global variables
let homeController;

// Application initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 AKI-SHOP Application Starting...');
    
    // Initialize MVC architecture
    initializeApp();
});

/**
 * Khởi tạo ứng dụng
 */
function initializeApp() {
    try {
        // Initialize Home Controller
        homeController = new HomeController();
        homeController.init();
        
        // Initialize global components
        initializeGlobalComponents();
        
        console.log('✅ Application initialized successfully');
    } catch (error) {
        console.error('❌ Application initialization failed:', error);
        showFallbackContent();
    }
}

/**
 * Khởi tạo các component global
 */
function initializeGlobalComponents() {
    // Scroll to top button
    initializeScrollToTop();
    
    // Performance monitoring
    initializePerformanceMonitoring();
    
    // Error handling
    initializeErrorHandling();
}

/**
 * Khởi tạo nút scroll to top
 */
function initializeScrollToTop() {
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        background: #2ecc71;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        z-index: 1000;
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(scrollBtn);
    
    // Show/hide scroll button
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });
    
    // Scroll to top functionality
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Khởi tạo performance monitoring
 */
function initializePerformanceMonitoring() {
    // Monitor page load time
    window.addEventListener('load', () => {
        const loadTime = performance.now();
        console.log(`📊 Page loaded in ${loadTime.toFixed(2)}ms`);
        
        // Log performance metrics
        if ('performance' in window && 'getEntriesByType' in performance) {
            const navigation = performance.getEntriesByType('navigation')[0];
            console.log('📈 Performance Metrics:', {
                'DOM Content Loaded': `${navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart}ms`,
                'Load Complete': `${navigation.loadEventEnd - navigation.loadEventStart}ms`,
                'Total Load Time': `${navigation.loadEventEnd - navigation.fetchStart}ms`
            });
        }
    });
}

/**
 * Khởi tạo error handling
 */
function initializeErrorHandling() {
    // Global error handler
    window.addEventListener('error', (event) => {
        console.error('🚨 Global Error:', event.error);
        // Có thể gửi error lên server hoặc hiển thị thông báo cho user
    });
    
    // Unhandled promise rejection handler
    window.addEventListener('unhandledrejection', (event) => {
        console.error('🚨 Unhandled Promise Rejection:', event.reason);
        event.preventDefault(); // Prevent console error
    });
}

/**
 * Hiển thị nội dung fallback khi có lỗi
 */
function showFallbackContent() {
    const app = document.getElementById('app');
    if (app) {
        app.innerHTML = `
            <div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
                <h1 style="color: #e74c3c;">⚠️ Có lỗi xảy ra</h1>
                <p>Không thể tải ứng dụng. Vui lòng thử lại sau.</p>
                <button onclick="location.reload()" style="
                    background: #3498db;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                ">Tải lại trang</button>
            </div>
        `;
    }
}

/**
 * Utility functions
 */
const AppUtils = {
    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    },
    
    /**
     * Format date
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    },
    
    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * Show loading spinner
     */
    showLoading(element) {
        if (element) {
            element.innerHTML = '<div class="loading-spinner">🔄 Đang tải...</div>';
        }
    },
    
    /**
     * Hide loading spinner
     */
    hideLoading(element, originalContent) {
        if (element) {
            element.innerHTML = originalContent || '';
        }
    }
};

// Export for global access
window.AppUtils = AppUtils;
