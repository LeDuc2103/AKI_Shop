document.addEventListener('DOMContentLoaded', function() {
    // Attach event listeners to pagination links
    attachPaginationEvents();
    
    function attachPaginationEvents() {
        const paginationLinks = document.querySelectorAll('#shop-pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                const category = url.searchParams.get('category') || '';
                loadProducts(page, category);
            });
        });
    }
    
    function loadProducts(page, category) {
        const xhr = new XMLHttpRequest();
        let ajaxUrl = 'shop-load-products.php?page=' + page;
        if (category) {
            ajaxUrl += '&category=' + category;
        }
        
        xhr.open('GET', ajaxUrl, true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    // Update products container
                    const productsContainer = document.getElementById('products-container');
                    if (productsContainer) {
                        productsContainer.innerHTML = response.products_html;
                    }
                    
                    // Update pagination
                    const paginationSection = document.getElementById('shop-pagination');
                    if (paginationSection) {
                        paginationSection.innerHTML = response.pagination_html;
                    }
                    
                    // Re-attach event listeners to new pagination links
                    attachPaginationEvents();
                    
                    // Smooth scroll to products section
                    const productSection = document.getElementById('product1');
                    if (productSection) {
                        productSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    
                    // Update URL without reloading page
                    const newUrl = new URL(window.location);
                    newUrl.searchParams.set('page', page);
                    if (category) {
                        newUrl.searchParams.set('category', category);
                    } else {
                        newUrl.searchParams.delete('category');
                    }
                    window.history.pushState({page: page, category: category}, '', newUrl);
                    
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                }
            } else {
                console.error('Error loading products:', xhr.status);
            }
        };
        
        xhr.onerror = function() {
            console.error('Request failed');
        };
        
        xhr.send();
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.page) {
            loadProducts(e.state.page, e.state.category || '');
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 1;
            const category = urlParams.get('category') || '';
            loadProducts(page, category);
        }
    });
});
