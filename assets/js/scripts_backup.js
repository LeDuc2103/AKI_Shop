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
            if (dots[slideIndex]) {
                dots[slideIndex].classList.add('active');
            }
            currentSlide = slideIndex;
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

// JavaScript for News Carousel
document.addEventListener('DOMContentLoaded', function() {
    const newsWrapper = document.getElementById('newsWrapper');
    const newsPrevBtn = document.getElementById('newsPrev');
    const newsNextBtn = document.getElementById('newsNext');
    
    if (newsWrapper && newsPrevBtn && newsNextBtn) {
        let newsCurrentIndex = 0;
        const newsCards = newsWrapper.children;
        let newsCardsToShow = getNewsCardsToShow();
        let newsCardWidth = getNewsCardWidth();
        
        function getNewsCardsToShow() {
            if (window.innerWidth <= 768) return 1;
            if (window.innerWidth <= 1200) return 2;
            return 3;
        }
        
        function getNewsCardWidth() {
            if (newsCards.length > 0) {
                return newsCards[0].offsetWidth + 20; // width + gap
            }
            return 350;
        }
        
        function updateNewsCarousel() {
            const translateX = -newsCurrentIndex * newsCardWidth;
            newsWrapper.style.transform = `translateX(${translateX}px)`;
        }
        
        function newsNextCard() {
            const maxIndex = Math.max(0, newsCards.length - newsCardsToShow);
            newsCurrentIndex = Math.min(newsCurrentIndex + 1, maxIndex);
            updateNewsCarousel();
        }
        
        function newsPrevCard() {
            newsCurrentIndex = Math.max(newsCurrentIndex - 1, 0);
            updateNewsCarousel();
        }
        
        // Event listeners
        newsNextBtn.addEventListener('click', newsNextCard);
        newsPrevBtn.addEventListener('click', newsPrevCard);
        
        // Touch/swipe support
        let newsStartX = 0;
        let newsEndX = 0;
        
        newsWrapper.addEventListener('touchstart', function(e) {
            newsStartX = e.touches[0].clientX;
        });
        
        newsWrapper.addEventListener('touchend', function(e) {
            newsEndX = e.changedTouches[0].clientX;
            handleNewsSwipe();
        });
        
        function handleNewsSwipe() {
            const swipeThreshold = 50;
            const diff = newsStartX - newsEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    newsNextCard();
                } else {
                    newsPrevCard();
                }
            }
        }
        
        // Update on window resize
        window.addEventListener('resize', function() {
            newsCardsToShow = getNewsCardsToShow();
            newsCardWidth = getNewsCardWidth();
            const maxIndex = Math.max(0, newsCards.length - newsCardsToShow);
            newsCurrentIndex = Math.min(newsCurrentIndex, maxIndex);
            updateNewsCarousel();
        });
        
        // Initialize
        updateNewsCarousel();
    }
});
        // Remove active class from all slides and dots
        slides.forEach((slide, index) => {
            slide.classList.remove('active');
            if (dots[index]) {
                dots[index].classList.remove('active');
            }
        });
        
        // Ensure slideIndex is within bounds
        currentSlide = slideIndex;
        if (currentSlide >= slides.length) {
            currentSlide = 0;
        } else if (currentSlide < 0) {
            currentSlide = slides.length - 1;
        }
        
        // Add active class to current slide and dot
        if (slides[currentSlide]) {
            slides[currentSlide].classList.add('active');
        }
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }

    // Function to go to next slide (sequential: 0->1->2->3->4->0)
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    // Auto-play carousel
    function startSlideshow() {
        slideInterval = setInterval(nextSlide, 4000); // Change slide every 4 seconds
    }

    // Stop auto-play
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

    // Initialize: Show first slide
    showSlide(0);
    
    // Start the slideshow
    startSlideshow();
});

// Hot Sale Carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.getElementById('hotSalePrev');
    const nextBtn = document.getElementById('hotSaleNext');
    const productsContainer = document.getElementById('hotSaleProducts');
    
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
        
        // Reset transition after instant move
        if (instant) {
            setTimeout(() => {
                productsContainer.style.transition = 'transform 0.5s ease';
            }, 50);
        }
    }
    
    function handleSeamlessLoop() {
        // Check if we need to reset position for seamless loop
        if (currentIndex >= originalProducts) {
            // We've reached the duplicate section, reset to beginning
            setTimeout(() => {
                isTransitioning = true;
                currentIndex = currentIndex - originalProducts;
                updateCarousel(true);
                setTimeout(() => {
                    isTransitioning = false;
                }, 100);
            }, 500);
        } else if (currentIndex < 0) {
            // We've gone before the beginning, jump to the end of original products
            setTimeout(() => {
                isTransitioning = true;
                currentIndex = originalProducts + currentIndex;
                updateCarousel(true);
                setTimeout(() => {
                    isTransitioning = false;
                }, 100);
            }, 500);
        }
    }

    function moveNext() {
        if (isTransitioning) return;
        
        // Move to next product (one product at a time)
        currentIndex++;
        updateCarousel();
        handleSeamlessLoop();
    }

    function movePrev() {
        if (isTransitioning) return;
        
        // Move to previous product (one product at a time)
        currentIndex--;
        updateCarousel();
        handleSeamlessLoop();
    }

    // Touch/Swipe event handlers
    function handleTouchStart(e) {
        if (window.innerWidth > 768) return; // Only enable swipe on mobile
        
        startX = e.touches[0].clientX;
        currentX = startX;
        startTime = Date.now();
        isDragging = true;
        
        // Disable transition during drag
        productsContainer.style.transition = 'none';
    }

    function handleTouchMove(e) {
        if (!isDragging || window.innerWidth > 768) return;
        
        currentX = e.touches[0].clientX;
        const diffX = currentX - startX;
        
        // Calculate current position
        const singleProductPercentage = 100 / totalProducts;
        const currentTranslateX = -(currentIndex * singleProductPercentage);
        const dragOffset = (diffX / productsContainer.offsetWidth) * 100;
        
        // Apply transform with drag offset
        productsContainer.style.transform = `translateX(${currentTranslateX + dragOffset}%)`;
        
        // Prevent default to avoid scrolling
        e.preventDefault();
    }

    function handleTouchEnd(e) {
        if (!isDragging || window.innerWidth > 768) return;
        
        isDragging = false;
        const diffX = currentX - startX;
        const diffTime = Date.now() - startTime;
        const velocity = Math.abs(diffX) / diffTime;
        
        // Re-enable transition
        productsContainer.style.transition = 'transform 0.5s ease';
        
        // Determine if swipe was significant enough
        const threshold = 50; // minimum distance for swipe
        const velocityThreshold = 0.3; // minimum velocity for swipe
        
        if ((Math.abs(diffX) > threshold || velocity > velocityThreshold) && diffTime < 300) {
            if (diffX > 0) {
                // Swipe right - go to previous product
                movePrev();
            } else {
                // Swipe left - go to next product
                moveNext();
            }
        } else {
            // Snap back to current position
            updateCarousel();
        }
    }

    if (nextBtn && prevBtn && productsContainer) {
        // Button event listeners
        nextBtn.addEventListener('click', moveNext);
        prevBtn.addEventListener('click', movePrev);

        // Touch event listeners for swipe functionality
        productsContainer.addEventListener('touchstart', handleTouchStart, { passive: false });
        productsContainer.addEventListener('touchmove', handleTouchMove, { passive: false });
        productsContainer.addEventListener('touchend', handleTouchEnd, { passive: false });

        // Prevent context menu on long press
        productsContainer.addEventListener('contextmenu', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (!isTransitioning) {
                updateCarousel();
            }
        });

        // Initialize - show first products (index 0)
        updateCarousel();
    }
});

// BOOX Showcase Carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    const booxShowcaseProducts = document.getElementById('booxShowcaseProducts');
    const booxPrevBtn = document.getElementById('booxShowcasePrev');
    const booxNextBtn = document.getElementById('booxShowcaseNext');
    
    if (booxShowcaseProducts && booxPrevBtn && booxNextBtn) {
        let booxCurrentIndex = 0;
        const booxProducts = booxShowcaseProducts.children;
        let booxProductsToShow = getBooxProductsToShow();
        let booxProductWidth = getBooxProductWidth();
        
        function getBooxProductsToShow() {
            if (window.innerWidth <= 480) return 1;
            if (window.innerWidth <= 768) return 2;
            if (window.innerWidth <= 1024) return 3;
            return 4;
        }
        
        function getBooxProductWidth() {
            if (booxProducts.length > 0) {
                return booxProducts[0].offsetWidth + 20; // width + gap
            }
            return 300;
        }
        
        function updateBooxCarousel() {
            const translateX = -booxCurrentIndex * booxProductWidth;
            booxShowcaseProducts.style.transform = `translateX(${translateX}px)`;
        }
        
        function booxNextProduct() {
            const maxIndex = Math.max(0, booxProducts.length - booxProductsToShow);
            booxCurrentIndex = Math.min(booxCurrentIndex + 1, maxIndex);
            updateBooxCarousel();
        }
        
        function booxPrevProduct() {
            booxCurrentIndex = Math.max(booxCurrentIndex - 1, 0);
            updateBooxCarousel();
        }
        
        // Event listeners
        booxNextBtn.addEventListener('click', booxNextProduct);
        booxPrevBtn.addEventListener('click', booxPrevProduct);
        
        // Touch/swipe support
        let booxStartX = 0;
        let booxEndX = 0;
        
        booxShowcaseProducts.addEventListener('touchstart', function(e) {
            booxStartX = e.touches[0].clientX;
        });
        
        booxShowcaseProducts.addEventListener('touchend', function(e) {
            booxEndX = e.changedTouches[0].clientX;
            handleBooxSwipe();
        });
        
        function handleBooxSwipe() {
            const swipeThreshold = 50;
            const diff = booxStartX - booxEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    booxNextProduct();
                } else {
                    booxPrevProduct();
                }
            }
        }
        
        // Update on window resize
        window.addEventListener('resize', function() {
            booxProductsToShow = getBooxProductsToShow();
            booxProductWidth = getBooxProductWidth();
            const maxIndex = Math.max(0, booxProducts.length - booxProductsToShow);
            booxCurrentIndex = Math.min(booxCurrentIndex, maxIndex);
            updateBooxCarousel();
        });
        
        // Initialize
        updateBooxCarousel();
    }
});

// Kindle Showcase Carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    const kindleShowcaseProducts = document.getElementById('kindleShowcaseProducts');
    const kindlePrevBtn = document.getElementById('kindleShowcasePrev');
    const kindleNextBtn = document.getElementById('kindleShowcaseNext');
    
    if (kindleShowcaseProducts && kindlePrevBtn && kindleNextBtn) {
        let kindleCurrentIndex = 0;
        const kindleProducts = kindleShowcaseProducts.children;
        let kindleProductsToShow = getKindleProductsToShow();
        let kindleProductWidth = getKindleProductWidth();
        
        function getKindleProductsToShow() {
            if (window.innerWidth <= 480) return 1;
            if (window.innerWidth <= 768) return 2;
            if (window.innerWidth <= 1024) return 3;
            return 4;
        }
        
        function getKindleProductWidth() {
            if (kindleProducts.length > 0) {
                return kindleProducts[0].offsetWidth + 20; // width + gap
            }
            return 300;
        }
        
        function updateKindleCarousel() {
            const translateX = -kindleCurrentIndex * kindleProductWidth;
            kindleShowcaseProducts.style.transform = `translateX(${translateX}px)`;
        }
        
        function kindleNextProduct() {
            const maxIndex = Math.max(0, kindleProducts.length - kindleProductsToShow);
            kindleCurrentIndex = Math.min(kindleCurrentIndex + 1, maxIndex);
            updateKindleCarousel();
        }
        
        function kindlePrevProduct() {
            kindleCurrentIndex = Math.max(kindleCurrentIndex - 1, 0);
            updateKindleCarousel();
        }
        
        // Event listeners
        kindleNextBtn.addEventListener('click', kindleNextProduct);
        kindlePrevBtn.addEventListener('click', kindlePrevProduct);
        
        // Touch/swipe support
        let kindleStartX = 0;
        let kindleEndX = 0;
        
        kindleShowcaseProducts.addEventListener('touchstart', function(e) {
            kindleStartX = e.touches[0].clientX;
        });
        
        kindleShowcaseProducts.addEventListener('touchend', function(e) {
            kindleEndX = e.changedTouches[0].clientX;
            handleKindleSwipe();
        });
        
        function handleKindleSwipe() {
            const swipeThreshold = 50;
            const diff = kindleStartX - kindleEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    kindleNextProduct();
                } else {
                    kindlePrevProduct();
                }
            }
        }
        
        // Update on window resize
        window.addEventListener('resize', function() {
            kindleProductsToShow = getKindleProductsToShow();
            kindleProductWidth = getKindleProductWidth();
            const maxIndex = Math.max(0, kindleProducts.length - kindleProductsToShow);
            kindleCurrentIndex = Math.min(kindleCurrentIndex, maxIndex);
            updateKindleCarousel();
        });
        
        // Initialize
        updateKindleCarousel();
    }
});

// Navbar Sticky Effect (only navbar becomes sticky, header stays visible)
document.addEventListener('DOMContentLoaded', function() {
    const header = document.getElementById('header');
    const navbarSection = document.getElementById('navbar-section');
    
    if (header && navbarSection) {
        let headerHeight = header.offsetHeight;
        let navbarHeight = navbarSection.offsetHeight;
        let isNavbarSticky = false;
        let ticking = false;
        
        function updateStickyNavbar() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const triggerPoint = headerHeight + 50; // When scroll past header
            
            // When scrolling past header, make only navbar sticky (keep header visible)
            if (scrollTop > triggerPoint && !isNavbarSticky) {
                navbarSection.classList.add('navbar-sticky');
                // Add padding to body to prevent content jump
                document.body.style.paddingTop = (headerHeight + navbarHeight) + 'px';
                isNavbarSticky = true;
                console.log('Navbar sticky activated at scroll:', scrollTop);
            } 
            // When scrolling back up, remove sticky
            else if (scrollTop <= triggerPoint && isNavbarSticky) {
                navbarSection.classList.remove('navbar-sticky');
                // Reset body padding
                document.body.style.paddingTop = '0px';
                isNavbarSticky = false;
                console.log('Navbar sticky deactivated at scroll:', scrollTop);
            }
            
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
                    document.body.style.paddingTop = (headerHeight + navbarHeight) + 'px';
                }
                console.log('Window resized - Header:', headerHeight, 'Navbar:', navbarHeight);
            }, 250);
        });
    }
});

// JavaScript for News Carousel
document.addEventListener('DOMContentLoaded', function() {
    const newsWrapper = document.getElementById('newsWrapper');
    const newsPrevBtn = document.getElementById('newsPrev');
    const newsNextBtn = document.getElementById('newsNext');
    
    if (newsWrapper && newsPrevBtn && newsNextBtn) {
        let newsCurrentIndex = 0;
        const newsCards = newsWrapper.children;
        let newsCardsToShow = getNewsCardsToShow();
        let newsCardWidth = getNewsCardWidth();
        
        function getNewsCardsToShow() {
            if (window.innerWidth <= 768) return 1;
            if (window.innerWidth <= 1200) return 2;
            return 3;
        }
        
        function getNewsCardWidth() {
            if (newsCards.length > 0) {
                return newsCards[0].offsetWidth + 20; // width + gap
            }
            return 350;
        }
        
        function updateNewsCarousel() {
            const translateX = -newsCurrentIndex * newsCardWidth;
            newsWrapper.style.transform = `translateX(${translateX}px)`;
        }
        
        function newsNextCard() {
            const maxIndex = Math.max(0, newsCards.length - newsCardsToShow);
            newsCurrentIndex = Math.min(newsCurrentIndex + 1, maxIndex);
            updateNewsCarousel();
        }
        
        function newsPrevCard() {
            newsCurrentIndex = Math.max(newsCurrentIndex - 1, 0);
            updateNewsCarousel();
        }
        
        // Event listeners
        newsNextBtn.addEventListener('click', newsNextCard);
        newsPrevBtn.addEventListener('click', newsPrevCard);
        
        // Touch/swipe support
        let newsStartX = 0;
        let newsEndX = 0;
        
        newsWrapper.addEventListener('touchstart', function(e) {
            newsStartX = e.touches[0].clientX;
        });
        
        newsWrapper.addEventListener('touchend', function(e) {
            newsEndX = e.changedTouches[0].clientX;
            handleNewsSwipe();
        });
        
        function handleNewsSwipe() {
            const swipeThreshold = 50;
            const diff = newsStartX - newsEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    newsNextCard();
                } else {
                    newsPrevCard();
                }
            }
        }
        
        // Update on window resize
        window.addEventListener('resize', function() {
            newsCardsToShow = getNewsCardsToShow();
            newsCardWidth = getNewsCardWidth();
            const maxIndex = Math.max(0, newsCards.length - newsCardsToShow);
            newsCurrentIndex = Math.min(newsCurrentIndex, maxIndex);
            updateNewsCarousel();
        });
        
        // Initialize
        updateNewsCarousel();
    }
});