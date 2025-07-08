jQuery(document).ready(function($) {
    
    // Inizializza il carosello
    initCarousel();
    
    // Inizializza masonry se presente
    initMasonry();
    
    // Funzione per inizializzare il carosello
    function initCarousel() {
        $('.trs-carousel').each(function() {
            var $carousel = $(this);
            var $wrapper = $carousel.find('.trs-carousel-wrapper');
            var $items = $carousel.find('.trs-review-item');
            var $prevBtn = $carousel.find('.trs-prev');
            var $nextBtn = $carousel.find('.trs-next');
            
            if ($items.length === 0) return;
            
            var currentIndex = 0;
            var itemsPerView = getItemsPerView();
            var maxIndex = Math.max(0, $items.length - itemsPerView);
            
            // Nascondi/mostra pulsanti di navigazione
            function updateNavButtons() {
                $prevBtn.toggle(currentIndex > 0);
                $nextBtn.toggle(currentIndex < maxIndex);
            }
            
            // Aggiorna la posizione del carosello
            function updateCarousel() {
                var translateX = -currentIndex * (100 / itemsPerView);
                $wrapper.css('transform', 'translateX(' + translateX + '%)');
                updateNavButtons();
            }
            
            // Calcola quanti elementi mostrare in base alla larghezza
            function getItemsPerView() {
                var width = $carousel.width();
                if (width < 480) return 1;
                if (width < 768) return 2;
                return 3;
            }
            
            // Event listeners per i pulsanti
            $prevBtn.on('click', function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateCarousel();
                }
            });
            
            $nextBtn.on('click', function() {
                if (currentIndex < maxIndex) {
                    currentIndex++;
                    updateCarousel();
                }
            });
            
            // Gestione touch/swipe per dispositivi mobili
            var startX = 0;
            var currentX = 0;
            
            $wrapper.on('touchstart', function(e) {
                startX = e.originalEvent.touches[0].clientX;
            });
            
            $wrapper.on('touchmove', function(e) {
                currentX = e.originalEvent.touches[0].clientX;
            });
            
            $wrapper.on('touchend', function() {
                var diffX = startX - currentX;
                var threshold = 50;
                
                if (Math.abs(diffX) > threshold) {
                    if (diffX > 0 && currentIndex < maxIndex) {
                        // Swipe sinistra - prossima
                        currentIndex++;
                        updateCarousel();
                    } else if (diffX < 0 && currentIndex > 0) {
                        // Swipe destra - precedente
                        currentIndex--;
                        updateCarousel();
                    }
                }
            });
            
            // Gestione ridimensionamento finestra
            $(window).on('resize', function() {
                var newItemsPerView = getItemsPerView();
                if (newItemsPerView !== itemsPerView) {
                    itemsPerView = newItemsPerView;
                    maxIndex = Math.max(0, $items.length - itemsPerView);
                    currentIndex = Math.min(currentIndex, maxIndex);
                    updateCarousel();
                }
            });
            
            // Inizializzazione
            updateNavButtons();
            
            // Auto-play opzionale
            if ($carousel.data('autoplay')) {
                var autoplayInterval = setInterval(function() {
                    if (currentIndex >= maxIndex) {
                        currentIndex = 0;
                    } else {
                        currentIndex++;
                    }
                    updateCarousel();
                }, 5000);
                
                // Pausa al hover
                $carousel.hover(
                    function() { clearInterval(autoplayInterval); },
                    function() {
                        autoplayInterval = setInterval(function() {
                            if (currentIndex >= maxIndex) {
                                currentIndex = 0;
                            } else {
                                currentIndex++;
                            }
                            updateCarousel();
                        }, 5000);
                    }
                );
            }
        });
    }
    
    // Funzione per inizializzare masonry
    function initMasonry() {
        if (typeof Masonry !== 'undefined') {
            $('.trs-masonry').each(function() {
                var $masonry = $(this);
                var masonry = new Masonry($masonry[0], {
                    itemSelector: '.trs-masonry-item',
                    columnWidth: '.trs-masonry-item',
                    percentPosition: true,
                    gutter: 20
                });
                
                // Rilayout quando le immagini sono caricate
                $masonry.find('img').on('load', function() {
                    masonry.layout();
                });
            });
        }
    }
    
    // Lazy loading per le recensioni
    function initLazyLoading() {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var $item = $(entry.target);
                    $item.addClass('trs-loaded');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        
        $('.trs-review-item, .trs-gallery-item, .trs-masonry-item, .trs-grid-item').each(function() {
            observer.observe(this);
        });
    }
    
    // Inizializza lazy loading
    initLazyLoading();
    
    // Funzione per aggiornare le recensioni via AJAX
    function refreshReviews(container, params) {
        var $container = $(container);
        
        $container.addClass('trs-loading');
        
        $.ajax({
            url: trs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'trs_get_reviews',
                params: params,
                nonce: trs_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $container.html(response.data.html);
                    initCarousel();
                    initMasonry();
                    initLazyLoading();
                } else {
                    $container.html('<div class="trs-error">Errore nel caricamento delle recensioni</div>');
                }
            },
            error: function() {
                $container.html('<div class="trs-error">Errore di connessione</div>');
            },
            complete: function() {
                $container.removeClass('trs-loading');
            }
        });
    }
    
    // Filtri per le recensioni
    $('.trs-filter').on('change', function() {
        var $container = $('.trs-reviews-container');
        var filterValue = $(this).val();
        
        if (filterValue === 'all') {
            $('.trs-review-item, .trs-gallery-item, .trs-masonry-item, .trs-grid-item').show();
        } else {
            $('.trs-review-item, .trs-gallery-item, .trs-masonry-item, .trs-grid-item').hide();
            $('[data-rating="' + filterValue + '"]').show();
        }
    });
    
    // Ricerca nelle recensioni
    $('.trs-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        var $items = $('.trs-review-item, .trs-gallery-item, .trs-masonry-item, .trs-grid-item');
        
        $items.each(function() {
            var $item = $(this);
            var text = $item.text().toLowerCase();
            
            if (text.includes(searchTerm)) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    });
    
    // Animazioni per il caricamento
    function animateReviews() {
        $('.trs-review-item, .trs-gallery-item, .trs-masonry-item, .trs-grid-item').each(function(index) {
            var $item = $(this);
            setTimeout(function() {
                $item.addClass('trs-animated');
            }, index * 100);
        });
    }
    
    // Inizializza animazioni
    animateReviews();
    
    // Gestione errori di caricamento immagini
    $('.trs-review-content img').on('error', function() {
        $(this).hide();
    });
    
    // Tooltip per le stelle del rating
    $('.trs-star').on('mouseenter', function() {
        var rating = $(this).index() + 1;
        var text = rating + ' stella' + (rating > 1 ? 'e' : '');
        $(this).attr('title', text);
    });
    
    // Condivisione social (se implementata)
    $('.trs-share').on('click', function(e) {
        e.preventDefault();
        var $item = $(this).closest('.trs-review-content');
        var text = $item.find('.trs-review-title').text();
        var url = window.location.href;
        
        // Esempio per Twitter
        var twitterUrl = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url);
        window.open(twitterUrl, '_blank', 'width=600,height=400');
    });
    
    // Gestione tema dinamico
    $('.trs-theme-toggle').on('click', function() {
        var $container = $('.trs-reviews-container');
        var currentTheme = $container.attr('data-theme') || 'default';
        var themes = ['default', 'dark', 'minimal', 'colored'];
        var currentIndex = themes.indexOf(currentTheme);
        var nextIndex = (currentIndex + 1) % themes.length;
        var nextTheme = themes[nextIndex];
        
        $container.removeClass('trs-theme-' + currentTheme);
        $container.addClass('trs-theme-' + nextTheme);
        $container.attr('data-theme', nextTheme);
        
        // Salva preferenza
        localStorage.setItem('trs-theme', nextTheme);
    });
    
    // Carica tema salvato
    var savedTheme = localStorage.getItem('trs-theme');
    if (savedTheme) {
        $('.trs-reviews-container').removeClass().addClass('trs-reviews-container trs-theme-' + savedTheme).attr('data-theme', savedTheme);
    }
    
    // Gestione accessibilità
    $('.trs-carousel-nav').on('keydown', function(e) {
        if (e.keyCode === 13 || e.keyCode === 32) { // Enter o Space
            e.preventDefault();
            $(this).click();
        }
    });
    
    // Focus management per il carosello
    $('.trs-carousel').on('keydown', function(e) {
        var $carousel = $(this);
        var $items = $carousel.find('.trs-review-item');
        var $focused = $carousel.find(':focus');
        
        if (e.keyCode === 37) { // Freccia sinistra
            e.preventDefault();
            var prevItem = $focused.prev('.trs-review-item');
            if (prevItem.length) {
                prevItem.focus();
            }
        } else if (e.keyCode === 39) { // Freccia destra
            e.preventDefault();
            var nextItem = $focused.next('.trs-review-item');
            if (nextItem.length) {
                nextItem.focus();
            }
        }
    });
    
    // Gestione responsive per il carosello
    function handleResponsiveCarousel() {
        $('.trs-carousel').each(function() {
            var $carousel = $(this);
            var width = $carousel.width();
            
            if (width < 480) {
                $carousel.addClass('trs-mobile');
            } else {
                $carousel.removeClass('trs-mobile');
            }
        });
    }
    
    $(window).on('resize', handleResponsiveCarousel);
    handleResponsiveCarousel();
    
    // Debug mode (solo per sviluppatori)
    if (window.location.search.includes('trs-debug=1')) {
        console.log('Trustpilot Reviews Scraper - Debug Mode');
        console.log('Carousel elements:', $('.trs-carousel').length);
        console.log('Review items:', $('.trs-review-item, .trs-gallery-item, .trs-masonry-item, .trs-grid-item').length);
    }
}); 