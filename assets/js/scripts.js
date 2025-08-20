// Hero Carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.nav-dot');
    let currentSlide = 0;
    let slideInterval;

    // Function to show specific slide
    function showSlide(slideIndex) {
        // Hide all slides
        slides.forEach((slide, index) => {
            slide.classList.remove('active');
            if (dots[index]) {
                dots[index].classList.remove('active');
            }
        });

        // Show current slide
        if (slides[slideIndex]) {
            slides[slideIndex].classList.add('active');
            currentSlide = slideIndex;
        }
        if (dots[slideIndex]) {
            dots[slideIndex].classList.add('active');
        }
    }

    // Function to go to next slide (smooth transition)
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    // Auto-play carousel every 4 seconds
    function startSlideshow() {
        slideInterval = setInterval(nextSlide, 4000);
    }

    function stopSlideshow() {
        clearInterval(slideInterval);
    }

    // Add click events to navigation dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            stopSlideshow();
            setTimeout(startSlideshow, 100); // Restart auto-play after short delay
        });
    });

    // Pause on hover
    const carousel = document.getElementById('hero-carousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', stopSlideshow);
        carousel.addEventListener('mouseleave', startSlideshow);
    }

    // Initialize: Show first slide if slides exist
    if (slides.length > 0) {
        showSlide(0);
        // Start the slideshow only if there are multiple slides
        if (slides.length > 1) {
            startSlideshow();
        }
    }
});

// Navbar Sticky Effect (hide header when scrolling, only navbar becomes sticky)
document.addEventListener('DOMContentLoaded', function() {
    const header = document.getElementById('header');
    const navbarSection = document.getElementById('navbar-section');
    
    if (header && navbarSection) {
        let headerHeight = header.offsetHeight;
        let navbarHeight = navbarSection.offsetHeight;
        let isNavbarSticky = false;
        let ticking = false;
        let lastScrollTop = 0;
        
        function updateStickyNavbar() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollDirection = scrollTop > lastScrollTop ? 'down' : 'up';
            const triggerPoint = headerHeight * 0.7; // Trigger when 70% of header is scrolled
            
            // When scrolling down past trigger point, hide header and make navbar sticky
            if (scrollTop > triggerPoint && scrollDirection === 'down' && !isNavbarSticky) {
                header.classList.add('header-hidden');
                navbarSection.classList.add('navbar-sticky');
                // Add padding to body to prevent content jump
                document.body.style.paddingTop = navbarHeight + 'px';
                isNavbarSticky = true;
                console.log('Navbar sticky activated - Header hidden at scroll:', scrollTop);
            } 
            // When scrolling back up to top, show header and remove sticky navbar
            else if (scrollTop <= triggerPoint && isNavbarSticky) {
                header.classList.remove('header-hidden');
                navbarSection.classList.remove('navbar-sticky');
                // Reset body padding
                document.body.style.paddingTop = '0px';
                isNavbarSticky = false;
                console.log('Navbar sticky deactivated - Header shown at scroll:', scrollTop);
            }
            
            lastScrollTop = scrollTop;
            ticking = false;
        }
        
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateStickyNavbar);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestTick, { passive: true });
        
        // Handle window resize to recalculate heights
        window.addEventListener('resize', function() {
            clearTimeout(window.resizeTimer);
            window.resizeTimer = setTimeout(function() {
                headerHeight = header.offsetHeight;
                navbarHeight = navbarSection.offsetHeight;
                
                if (isNavbarSticky) {
                    document.body.style.paddingTop = navbarHeight + 'px';
                } else {
                    document.body.style.paddingTop = '0px';
                }
                console.log('Window resized - Header:', headerHeight, 'Navbar:', navbarHeight);
            }, 250);
        });
    }
});

// Hot Sale Carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.getElementById('hotSalePrev');
    const nextBtn = document.getElementById('hotSaleNext');
    const productsContainer = document.getElementById('hotSaleProducts');
    
    if (!prevBtn || !nextBtn || !productsContainer) return;
    
    let currentIndex = 0;
    let visibleProducts = 5;
    const originalProducts = 10;
    const totalProducts = 15; // 10 original + 5 duplicates
    let isTransitioning = false;
    
    // Touch/Swipe variables
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    let startTime = 0;
    
    function getVisibleProducts() {
        if (window.innerWidth <= 768) {
            return 1; // Mobile: show 1 product at a time
        } else if (window.innerWidth <= 1024) {
            return 3; // Tablet: show 3 products at a time
        } else {
            return 5; // Desktop: show 5 products at a time
        }
    }
    
    function updateCarousel(instant = false) {
        if (isTransitioning) return;
        
        visibleProducts = getVisibleProducts();
        
        // Calculate the exact percentage to move based on total width
        // Each product takes up (100/15)% of total width
        const singleProductPercentage = 100 / totalProducts;
        const translateX = -(currentIndex * singleProductPercentage);
        
        if (instant) {
            productsContainer.style.transition = 'none';
        } else {
            productsContainer.style.transition = 'transform 0.5s ease';
        }
        
        productsContainer.style.transform = `translateX(${translateX}%)`;
        
        // Handle infinite scrolling
        if (!instant) {
            isTransitioning = true;
            setTimeout(() => {
                if (currentIndex >= originalProducts) {
                    currentIndex = 0;
                    updateCarousel(true);
                }
                isTransitioning = false;
            }, 500);
        }
    }
    
    function nextProduct() {
        if (isTransitioning) return;
        currentIndex++;
        updateCarousel();
    }
    
    function prevProduct() {
        if (isTransitioning) return;
        if (currentIndex <= 0) {
            currentIndex = originalProducts;
            updateCarousel(true);
            setTimeout(() => {
                currentIndex--;
                updateCarousel();
            }, 50);
        } else {
            currentIndex--;
            updateCarousel();
        }
    }
    
    // Event listeners for buttons
    nextBtn.addEventListener('click', nextProduct);
    prevBtn.addEventListener('click', prevProduct);
    
    // Touch/Swipe support
    productsContainer.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        currentX = startX;
        startTime = Date.now();
        isDragging = true;
    });
    
    productsContainer.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        currentX = e.touches[0].clientX;
        e.preventDefault();
    });
    
    productsContainer.addEventListener('touchend', () => {
        if (!isDragging) return;
        isDragging = false;
        
        const deltaX = startX - currentX;
        const deltaTime = Date.now() - startTime;
        const velocity = Math.abs(deltaX) / deltaTime;
        
        // Only trigger swipe if it's fast enough or moved far enough
        if (velocity > 0.5 || Math.abs(deltaX) > 50) {
            if (deltaX > 0) {
                nextProduct();
            } else {
                prevProduct();
            }
        }
    });
    
    // Auto-play
    let autoplayInterval;
    
    function startAutoplay() {
        autoplayInterval = setInterval(nextProduct, 4000);
    }
    
    function stopAutoplay() {
        clearInterval(autoplayInterval);
    }
    
    // Pause on hover
    productsContainer.addEventListener('mouseenter', stopAutoplay);
    productsContainer.addEventListener('mouseleave', startAutoplay);
    
    // Handle window resize
    window.addEventListener('resize', () => {
        updateCarousel(true);
    });
    
    // Initialize
    updateCarousel(true);
    startAutoplay();
});

// Additional product carousels (Boox, Kindle, Kobo) can be added here with similar pattern
