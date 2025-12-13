// Mobile Responsive JavaScript for AKI Store

document.addEventListener('DOMContentLoaded', function() {
    
    // ===================================
    // MOBILE MENU TOGGLE
    // ===================================
    const bar = document.getElementById('bar');
    const close = document.getElementById('close');
    const nav = document.getElementById('navbar');
    
    if (bar) {
        bar.addEventListener('click', function() {
            nav.classList.add('active');
        });
    }
    
    if (close) {
        close.addEventListener('click', function() {
            nav.classList.remove('active');
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (nav && nav.classList.contains('active')) {
            const isClickInsideNav = nav.contains(event.target);
            const isClickOnBar = bar && bar.contains(event.target);
            
            if (!isClickInsideNav && !isClickOnBar) {
                nav.classList.remove('active');
            }
        }
    });
    
    // ===================================
    // USER DROPDOWN MOBILE
    // ===================================
    const userIcon = document.getElementById('user-icon');
    const userDropdown = userIcon ? userIcon.querySelector('.user-dropdown') : null;
    
    if (userIcon && window.innerWidth <= 767) {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'user-dropdown-overlay';
        document.body.appendChild(overlay);
        
        // Toggle dropdown on click
        userIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.toggle('active');
            overlay.style.display = this.classList.contains('active') ? 'block' : 'none';
        });
        
        // Close dropdown when clicking overlay
        overlay.addEventListener('click', function() {
            userIcon.classList.remove('active');
            this.style.display = 'none';
        });
        
        // Close dropdown when clicking a link
        if (userDropdown) {
            const dropdownLinks = userDropdown.querySelectorAll('a');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', function() {
                    userIcon.classList.remove('active');
                    overlay.style.display = 'none';
                });
            });
        }
    }
    
    // ===================================
    // SEARCH BOX TOGGLE MOBILE
    // ===================================
    const searchIcon = document.getElementById('search-icon');
    const searchBox = document.querySelector('.search-box');
    
    if (searchIcon && searchBox && window.innerWidth <= 767) {
        searchBox.classList.add('hidden');
        
        searchIcon.addEventListener('click', function(e) {
            e.preventDefault();
            searchBox.classList.toggle('hidden');
        });
    }
    
    // ===================================
    // RESPONSIVE TABLE - CART
    // ===================================
    const cartTable = document.querySelector('#cart table');
    if (cartTable && window.innerWidth <= 767) {
        const tbody = cartTable.querySelector('tbody');
        if (tbody) {
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    const headers = cartTable.querySelectorAll('thead th');
                    if (headers[index]) {
                        cell.setAttribute('data-label', headers[index].textContent);
                    }
                });
            });
        }
    }
    
    // ===================================
    // STICKY HEADER ON SCROLL (Mobile)
    // ===================================
    if (window.innerWidth <= 767) {
        const header = document.getElementById('header');
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('sticky-mobile');
            } else {
                header.classList.remove('sticky-mobile');
            }
            
            lastScroll = currentScroll;
        });
    }
    
    // ===================================
    // PREVENT ZOOM ON INPUT FOCUS (iOS)
    // ===================================
    if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (input.style.fontSize === '' || parseInt(input.style.fontSize) < 16) {
                input.style.fontSize = '16px';
            }
        });
    }
    
    // ===================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ===================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Close mobile menu if open
                    if (nav) {
                        nav.classList.remove('active');
                    }
                }
            }
        });
    });
    
    // ===================================
    // TOUCH SWIPE FOR IMAGE GALLERY (MOBILE)
    // ===================================
    const imageGallery = document.querySelector('.single-pro-image');
    if (imageGallery && window.innerWidth <= 767) {
        let touchStartX = 0;
        let touchEndX = 0;
        
        imageGallery.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        imageGallery.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next image
                    const smallImgs = imageGallery.querySelectorAll('.small-img');
                    const mainImg = imageGallery.querySelector('.MainImg');
                    if (smallImgs.length > 0 && mainImg) {
                        const currentSrc = mainImg.src;
                        let currentIndex = -1;
                        smallImgs.forEach((img, index) => {
                            if (img.src === currentSrc) {
                                currentIndex = index;
                            }
                        });
                        const nextIndex = (currentIndex + 1) % smallImgs.length;
                        mainImg.src = smallImgs[nextIndex].src;
                    }
                } else {
                    // Swipe right - previous image
                    const smallImgs = imageGallery.querySelectorAll('.small-img');
                    const mainImg = imageGallery.querySelector('.MainImg');
                    if (smallImgs.length > 0 && mainImg) {
                        const currentSrc = mainImg.src;
                        let currentIndex = -1;
                        smallImgs.forEach((img, index) => {
                            if (img.src === currentSrc) {
                                currentIndex = index;
                            }
                        });
                        const prevIndex = currentIndex - 1 < 0 ? smallImgs.length - 1 : currentIndex - 1;
                        mainImg.src = smallImgs[prevIndex].src;
                    }
                }
            }
        }
    }
    
    // ===================================
    // RESIZE EVENT HANDLER
    // ===================================
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Reload page if switching between mobile and desktop
            const isMobile = window.innerWidth <= 767;
            const wasMobile = document.body.classList.contains('mobile-view');
            
            if (isMobile !== wasMobile) {
                location.reload();
            }
        }, 250);
    });
    
    // Set initial mobile class
    if (window.innerWidth <= 767) {
        document.body.classList.add('mobile-view');
    }
    
});

// ===================================
// PERFORMANCE OPTIMIZATION
// ===================================

// Lazy load images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img.lazy').forEach(img => {
        imageObserver.observe(img);
    });
}
