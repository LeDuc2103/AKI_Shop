/**
 * Home Presenter - Điều khiển logic và tương tác giữa Model và View
 */
class HomePresenter {
    constructor() {
        this.productModel = new ProductModel();
        this.newsModel = new NewsModel();
        this.currentView = null;
    }

    /**
     * Khởi tạo trang chủ
     */
    init() {
        this.loadHomeView();
        this.bindEvents();
    }

    /**
     * Load view trang chủ
     */
    async loadHomeView() {
        try {
            const response = await fetch('src/views/index.html');
            const html = await response.text();
            document.getElementById('app').innerHTML = html;
            
            // Render dữ liệu
            this.renderHotSaleProducts();
            this.renderAccessories();
            this.renderNews();
            
            // Khởi tạo các component
            this.initializeCarousels();
            this.initializeSearch();
        } catch (error) {
            console.error('Error loading home view:', error);
            this.showErrorMessage('Không thể tải trang chủ');
        }
    }

    /**
     * Render danh sách sản phẩm hot sale
     */
    renderHotSaleProducts() {
        const products = this.productModel.getHotSaleProducts();
        const container = document.getElementById('hotSaleProducts');
        
        if (!container) return;

        container.innerHTML = products.map(product => `
            <div class="product-card" data-id="${product.id}">
                <div class="product-image">
                    <div class="discount-badge">${product.discount}</div>
                    <img src="${product.image}" alt="${product.name}" loading="lazy">
                </div>
                <div class="product-rating">
                    ${'<i class="fas fa-star"></i>'.repeat(product.rating)}
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <div class="product-price">
                        <span class="current-price">${product.currentPrice}</span>
                        <span class="old-price">${product.oldPrice}</span>
                    </div>
                    <button class="buy-button" onclick="homePresenter.addToCart(${product.id})">MUA</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Render danh sách phụ kiện
     */
    renderAccessories() {
        const accessories = this.productModel.getAccessories();
        const container = document.querySelector('.accessories-container');
        
        if (!container) return;

        container.innerHTML = accessories.map(accessory => `
            <div class="accessories-product" data-id="${accessory.id}">
                <div class="product-image">
                    <div class="discount-badge">${accessory.discount}</div>
                    <img src="${accessory.image}" alt="${accessory.name}" loading="lazy">
                </div>
                <div class="product-rating">
                    ${'<i class="fas fa-star"></i>'.repeat(accessory.rating)}
                </div>
                <div class="product-info">
                    <h3>${accessory.name}</h3>
                    <div class="product-price">
                        <span class="current-price">${accessory.currentPrice}</span>
                        <span class="old-price">${accessory.oldPrice}</span>
                    </div>
                    <button class="buy-button" onclick="homePresenter.addToCart(${accessory.id})">MUA</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Render tin tức
     */
    renderNews() {
        const news = this.newsModel.getLatestNews();
        const container = document.getElementById('newsWrapper');
        
        if (!container) return;

        container.innerHTML = news.map(article => `
            <div class="news-card" data-id="${article.id}">
                <div class="news-image">
                    <img src="${article.image}" alt="${article.title}" loading="lazy">
                </div>
                <div class="news-content">
                    <h3>${article.title}</h3>
                    <div class="news-meta">
                        <span class="news-date">
                            <i class="far fa-clock"></i> ${article.date}
                        </span>
                        <span class="news-views">
                            <i class="far fa-eye"></i> ${article.views} lượt xem
                        </span>
                    </div>
                    <p class="news-excerpt">${article.excerpt}</p>
                </div>
            </div>
        `).join('');
    }

    /**
     * Khởi tạo carousel components
     */
    initializeCarousels() {
        // Hot Sale Carousel đã có trong scripts.js
        // News Carousel đã có trong scripts.js
        // Accessories không cần carousel vì sử dụng flex-wrap
    }

    /**
     * Khởi tạo tìm kiếm
     */
    initializeSearch() {
        const searchInput = document.querySelector('.search-input');
        const searchButton = document.querySelector('.search-button');
        
        if (searchInput && searchButton) {
            searchButton.addEventListener('click', () => {
                this.performSearch(searchInput.value);
            });
            
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(searchInput.value);
                }
            });
        }
    }

    /**
     * Thực hiện tìm kiếm
     */
    performSearch(keyword) {
        if (!keyword.trim()) return;
        
        const products = this.productModel.searchProducts(keyword);
        const news = this.newsModel.searchNews(keyword);
        
        console.log('Search results:', { products, news });
        // Có thể implement hiển thị kết quả tìm kiếm ở đây
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    addToCart(productId) {
        const product = this.productModel.getProductById(productId);
        if (product) {
            console.log('Added to cart:', product);
            this.showSuccessMessage(`Đã thêm "${product.name}" vào giỏ hàng`);
            // Implement logic thêm vào giỏ hàng
        }
    }

    /**
     * Bind events
     */
    bindEvents() {
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navbar = document.querySelector('#navbar');
        
        if (mobileMenuBtn && navbar) {
            mobileMenuBtn.addEventListener('click', () => {
                navbar.classList.toggle('active');
            });
        }

        // Category filter
        document.addEventListener('click', (e) => {
            if (e.target.matches('.category-btn')) {
                const category = e.target.dataset.category;
                this.filterByCategory(category);
            }
        });
    }

    /**
     * Lọc sản phẩm theo category
     */
    filterByCategory(category) {
        const products = this.productModel.getProductsByCategory(category);
        // Implement hiển thị sản phẩm đã lọc
        console.log('Filtered products:', products);
    }

    /**
     * Hiển thị thông báo thành công
     */
    showSuccessMessage(message) {
        // Implement toast notification
        console.log('Success:', message);
    }

    /**
     * Hiển thị thông báo lỗi
     */
    showErrorMessage(message) {
        // Implement error notification
        console.error('Error:', message);
    }
}
