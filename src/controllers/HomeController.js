/**
 * Home Controller - Điều khiển logic và tương tác trong mô hình MVC
 */
class HomeController {
    constructor() {
        this.productModel = new ProductModel();
        this.newsModel = new NewsModel();
        this.view = null;
    }

    /**
     * Khởi tạo controller
     */
    init() {
        this.loadView();
        this.bindEvents();
        this.renderData();
    }

    /**
     * Load view và khởi tạo
     */
    async loadView() {
        try {
            const response = await fetch('src/views/index.html');
            const html = await response.text();
            document.getElementById('app').innerHTML = html;
            
            // Khởi tạo các component sau khi load view
            this.initializeComponents();
        } catch (error) {
            console.error('Error loading view:', error);
            this.showErrorMessage('Không thể tải giao diện');
        }
    }

    /**
     * Render tất cả dữ liệu
     */
    renderData() {
        this.renderHotSaleProducts();
        this.renderAccessories();
        this.renderNews();
        this.renderBrandShowcases();
    }

    /**
     * Render sản phẩm hot sale
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
                    <button class="buy-button" onclick="homeController.addToCart(${product.id})">MUA</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Render phụ kiện
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
                    <button class="buy-button" onclick="homeController.addToCart(${accessory.id})">MUA</button>
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
     * Render brand showcases
     */
    renderBrandShowcases() {
        this.renderBooxShowcase();
        this.renderKindleShowcase();
        this.renderKoboShowcase();
        this.renderRemarkableShowcase();
    }

    /**
     * Render Boox showcase
     */
    renderBooxShowcase() {
        const products = this.productModel.getProductsByBrand('boox');
        const container = document.getElementById('booxShowcaseProducts');
        
        if (!container) return;

        container.innerHTML = products.map(product => `
            <div class="showcase-product" data-id="${product.id}">
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
                    <button class="buy-button" onclick="homeController.addToCart(${product.id})">MUA</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Render Kindle showcase
     */
    renderKindleShowcase() {
        const products = this.productModel.getProductsByBrand('kindle');
        const container = document.getElementById('kindleShowcaseProducts');
        
        if (!container) return;

        container.innerHTML = products.map(product => `
            <div class="showcase-product" data-id="${product.id}">
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
                    <button class="buy-button" onclick="homeController.addToCart(${product.id})">MUA</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Render Kobo showcase
     */
    renderKoboShowcase() {
        const products = this.productModel.getProductsByBrand('kobo');
        const container = document.getElementById('koboShowcaseProducts');
        
        if (!container) return;

        container.innerHTML = products.map(product => `
            <div class="showcase-product" data-id="${product.id}">
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
                    <button class="buy-button" onclick="homeController.addToCart(${product.id})">MUA</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Render reMarkable showcase
     */
    renderRemarkableShowcase() {
        const products = this.productModel.getProductsByBrand('remarkable');
        const container = document.getElementById('remarkableShowcaseProducts');
        
        if (!container) return;

        container.innerHTML = products.map(product => `
            <div class="showcase-product" data-id="${product.id}">
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
                    <button class="buy-button" onclick="homeController.addToCart(${product.id})">MUA</button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Khởi tạo các component
     */
    initializeComponents() {
        this.initializeCarousels();
        this.initializeSearch();
        this.initializeMobileMenu();
    }

    /**
     * Khởi tạo carousel
     */
    initializeCarousels() {
        // Carousel code đã có trong scripts.js
        console.log('Carousels initialized');
    }

    /**
     * Khởi tạo tìm kiếm
     */
    initializeSearch() {
        const searchInput = document.querySelector('.search-input');
        const searchButton = document.querySelector('.search-btn');
        
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
     * Khởi tạo mobile menu
     */
    initializeMobileMenu() {
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navbar = document.querySelector('#navbar');
        
        if (mobileMenuBtn && navbar) {
            mobileMenuBtn.addEventListener('click', () => {
                navbar.classList.toggle('active');
            });
        }
    }

    /**
     * Bind events
     */
    bindEvents() {
        // Event delegation cho product clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('.buy-button')) {
                const productCard = e.target.closest('[data-id]');
                const productId = parseInt(productCard.dataset.id);
                this.addToCart(productId);
            }
            
            if (e.target.matches('.category-btn')) {
                const category = e.target.dataset.category;
                this.filterByCategory(category);
            }
        });

        // Search events
        const searchForm = document.querySelector('.search-container');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const searchInput = searchForm.querySelector('input');
                this.performSearch(searchInput.value);
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
        
        console.log('Search results:', { keyword, products, news });
        this.showSearchResults(products, news);
    }

    /**
     * Hiển thị kết quả tìm kiếm
     */
    showSearchResults(products, news) {
        // Implement search results view
        console.log('Showing search results...');
    }

    /**
     * Lọc theo category
     */
    filterByCategory(category) {
        const products = this.productModel.getProductsByCategory(category);
        console.log('Filtered products:', products);
        // Implement category filter view
    }

    /**
     * Thêm vào giỏ hàng
     */
    addToCart(productId) {
        const product = this.productModel.getProductById(productId);
        if (product) {
            console.log('Added to cart:', product);
            this.showSuccessMessage(`Đã thêm "${product.name}" vào giỏ hàng`);
            // Implement cart logic
        }
    }

    /**
     * Hiển thị thông báo thành công
     */
    showSuccessMessage(message) {
        console.log('Success:', message);
        // Implement toast notification
    }

    /**
     * Hiển thị thông báo lỗi
     */
    showErrorMessage(message) {
        console.error('Error:', message);
        // Implement error notification
    }
}
