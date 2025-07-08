<?php
/**
 * Esempi di utilizzo del plugin Trustpilot Reviews Scraper
 * 
 * Questo file mostra come utilizzare il plugin in diversi scenari
 */

// ===== ESEMPIO 1: Shortcode base =====
// Inserisci questo in una pagina o post WordPress
echo do_shortcode('[trustpilot_reviews]');

// ===== ESEMPIO 2: Shortcode con parametri personalizzati =====
echo do_shortcode('[trustpilot_reviews style="carousel" limit="4" show_rating="true" show_date="true" show_author="true"]');

// ===== ESEMPIO 3: Galleria di recensioni =====
echo do_shortcode('[trustpilot_reviews style="gallery" limit="8"]');

// ===== ESEMPIO 4: Layout masonry =====
echo do_shortcode('[trustpilot_reviews style="masonry" limit="12"]');

// ===== ESEMPIO 5: Grid semplice =====
echo do_shortcode('[trustpilot_reviews style="grid" limit="6"]');

// ===== ESEMPIO 6: Solo rating e autore =====
echo do_shortcode('[trustpilot_reviews show_rating="true" show_author="true" show_date="false"]');

// ===== ESEMPIO 7: Solo contenuto e rating =====
echo do_shortcode('[trustpilot_reviews show_rating="true" show_date="false" show_author="false"]');

// ===== ESEMPIO 8: Molte recensioni in galleria =====
echo do_shortcode('[trustpilot_reviews style="gallery" limit="20"]');

// ===== ESEMPIO 9: Carosello con poche recensioni =====
echo do_shortcode('[trustpilot_reviews style="carousel" limit="3"]');

// ===== ESEMPIO 10: Layout responsive per mobile =====
echo do_shortcode('[trustpilot_reviews style="grid" limit="4"]');

// ===== ESEMPIO 11: Utilizzo in template PHP =====
// In un file template del tema
function display_trustpilot_reviews_section() {
    ?>
    <section class="reviews-section">
        <div class="container">
            <h2>Le Nostre Recensioni</h2>
            <p>Scopri cosa dicono i nostri clienti su Trustpilot</p>
            
            <?php echo do_shortcode('[trustpilot_reviews style="carousel" limit="6"]'); ?>
            
            <div class="reviews-cta">
                <a href="https://www.trustpilot.com/review/tua-azienda.com" 
                   target="_blank" class="btn btn-primary">
                    Lascia una Recensione
                </a>
            </div>
        </div>
    </section>
    <?php
}

// ===== ESEMPIO 12: Sezione recensioni con filtri =====
function display_reviews_with_filters() {
    ?>
    <div class="reviews-page">
        <div class="reviews-header">
            <h1>Recensioni dei Nostri Clienti</h1>
            
            <div class="reviews-filters">
                <select class="trs-filter">
                    <option value="all">Tutte le recensioni</option>
                    <option value="5">5 stelle</option>
                    <option value="4">4 stelle</option>
                    <option value="3">3 stelle</option>
                    <option value="2">2 stelle</option>
                    <option value="1">1 stella</option>
                </select>
                
                <input type="text" class="trs-search" placeholder="Cerca nelle recensioni...">
            </div>
        </div>
        
        <div class="reviews-content">
            <?php echo do_shortcode('[trustpilot_reviews style="gallery" limit="12"]'); ?>
        </div>
    </div>
    <?php
}

// ===== ESEMPIO 13: Homepage hero con recensioni =====
function display_homepage_reviews_hero() {
    ?>
    <section class="hero-reviews">
        <div class="hero-content">
            <h1>La Tua Azienda</h1>
            <p>Servizi eccellenti, clienti soddisfatti</p>
            
            <div class="hero-reviews-carousel">
                <?php echo do_shortcode('[trustpilot_reviews style="carousel" limit="3" show_author="false"]'); ?>
            </div>
            
            <div class="hero-cta">
                <a href="#services" class="btn btn-primary">I Nostri Servizi</a>
                <a href="#contact" class="btn btn-secondary">Contattaci</a>
            </div>
        </div>
    </section>
    <?php
}

// ===== ESEMPIO 14: Footer con recensioni =====
function display_footer_reviews() {
    ?>
    <footer class="site-footer">
        <div class="footer-reviews">
            <h3>Dicono di Noi</h3>
            <?php echo do_shortcode('[trustpilot_reviews style="grid" limit="4" show_date="false"]'); ?>
        </div>
        
        <div class="footer-links">
            <!-- Altri link del footer -->
        </div>
    </footer>
    <?php
}

// ===== ESEMPIO 15: Pagina dedicata alle recensioni =====
function display_reviews_page() {
    ?>
    <div class="reviews-page-template">
        <header class="page-header">
            <h1>Recensioni Trustpilot</h1>
            <p>Scopri cosa dicono i nostri clienti sui nostri servizi</p>
        </header>
        
        <div class="reviews-stats">
            <div class="stat-item">
                <span class="stat-number">4.8</span>
                <span class="stat-label">Rating Medio</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">500+</span>
                <span class="stat-label">Recensioni</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">98%</span>
                <span class="stat-label">Clienti Soddisfatti</span>
            </div>
        </div>
        
        <div class="reviews-grid">
            <?php echo do_shortcode('[trustpilot_reviews style="masonry" limit="20"]'); ?>
        </div>
        
        <div class="reviews-cta">
            <h2>Hai esperienze con noi?</h2>
            <p>Condividi la tua opinione su Trustpilot</p>
            <a href="https://www.trustpilot.com/review/tua-azienda.com" 
               target="_blank" class="btn btn-primary">
                Lascia una Recensione
            </a>
        </div>
    </div>
    <?php
}

// ===== ESEMPIO 16: Widget sidebar =====
function display_sidebar_reviews_widget() {
    ?>
    <div class="sidebar-widget reviews-widget">
        <h3 class="widget-title">Recensioni Recenti</h3>
        <?php echo do_shortcode('[trustpilot_reviews style="grid" limit="3" show_date="false"]'); ?>
        
        <div class="widget-footer">
            <a href="/recensioni" class="widget-link">Vedi tutte le recensioni</a>
        </div>
    </div>
    <?php
}

// ===== ESEMPIO 17: Popup recensioni =====
function display_reviews_popup() {
    ?>
    <div id="reviews-popup" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Le Nostre Recensioni</h2>
            <?php echo do_shortcode('[trustpilot_reviews style="carousel" limit="6"]'); ?>
        </div>
    </div>
    
    <script>
    // JavaScript per aprire il popup
    document.getElementById('open-reviews').addEventListener('click', function() {
        document.getElementById('reviews-popup').style.display = 'block';
    });
    
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('reviews-popup').style.display = 'none';
    });
    </script>
    <?php
}

// ===== ESEMPIO 18: Sezione prodotti con recensioni =====
function display_product_reviews() {
    ?>
    <div class="product-reviews">
        <h3>Recensioni del Prodotto</h3>
        <div class="product-rating">
            <span class="stars">★★★★★</span>
            <span class="rating-text">4.8 su 5 stelle</span>
        </div>
        
        <?php echo do_shortcode('[trustpilot_reviews style="gallery" limit="4"]'); ?>
        
        <div class="product-reviews-link">
            <a href="/recensioni-prodotto" class="btn btn-outline">
                Leggi tutte le recensioni
            </a>
        </div>
    </div>
    <?php
}

// ===== ESEMPIO 19: Landing page con recensioni =====
function display_landing_page_reviews() {
    ?>
    <section class="landing-reviews">
        <div class="container">
            <div class="reviews-header">
                <h2>Perché i Clienti Scegliono Noi</h2>
                <p>Scopri le esperienze dei nostri clienti soddisfatti</p>
            </div>
            
            <div class="reviews-showcase">
                <?php echo do_shortcode('[trustpilot_reviews style="carousel" limit="5"]'); ?>
            </div>
            
            <div class="reviews-cta">
                <a href="#contact" class="btn btn-primary btn-large">
                    Inizia Ora
                </a>
            </div>
        </div>
    </section>
    <?php
}

// ===== ESEMPIO 20: Blog post con recensioni =====
function display_blog_post_reviews() {
    ?>
    <article class="blog-post">
        <header class="post-header">
            <h1>I Nostri Servizi</h1>
            <div class="post-meta">
                <span class="post-date">15 Dicembre 2023</span>
                <span class="post-author">Admin</span>
            </div>
        </header>
        
        <div class="post-content">
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
            
            <h2>Cosa Dicono i Nostri Clienti</h2>
            <?php echo do_shortcode('[trustpilot_reviews style="grid" limit="3"]'); ?>
            
            <p>Continua a leggere...</p>
        </div>
    </article>
    <?php
}

// ===== ESEMPIO 21: CSS personalizzato per i temi =====
function custom_reviews_css() {
    ?>
    <style>
    /* Tema personalizzato per le recensioni */
    .trs-reviews-container {
        --primary-color: #007cba;
        --secondary-color: #f8f9fa;
        --text-color: #333;
        --border-color: #e9ecef;
    }
    
    .trs-review-content {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .trs-review-content:hover {
        border-color: var(--primary-color);
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 124, 186, 0.2);
    }
    
    .trs-star.filled {
        color: #ffd700;
        text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
    }
    
    .trs-review-title {
        color: var(--primary-color);
        font-weight: 700;
    }
    
    .trs-author {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    /* Responsive design personalizzato */
    @media (max-width: 768px) {
        .trs-review-content {
            margin-bottom: 20px;
        }
        
        .trs-carousel-nav {
            width: 30px;
            height: 30px;
            font-size: 14px;
        }
    }
    </style>
    <?php
}

// ===== ESEMPIO 22: JavaScript personalizzato =====
function custom_reviews_js() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Animazione personalizzata per le recensioni
        $('.trs-review-content').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(30px)'
            });
            
            setTimeout(function() {
                $(this).animate({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                }, 600);
            }.bind(this), index * 200);
        });
        
        // Effetto hover personalizzato
        $('.trs-review-content').hover(
            function() {
                $(this).find('.trs-review-title').css('color', '#007cba');
            },
            function() {
                $(this).find('.trs-review-title').css('color', '#333');
            }
        );
        
        // Contatore recensioni
        var reviewCount = $('.trs-review-item').length;
        $('.reviews-counter').text(reviewCount + ' recensioni');
    });
    </script>
    <?php
}

// ===== ESEMPIO 23: Hook personalizzati =====
// Aggiungi questo nel file functions.php del tema

// Modifica l'output delle recensioni
add_filter('trs_reviews_html', 'custom_reviews_output', 10, 2);
function custom_reviews_output($html, $reviews) {
    // Aggiungi una classe personalizzata
    $html = str_replace('trs-reviews-container', 'trs-reviews-container custom-theme', $html);
    
    // Aggiungi un badge per le recensioni recenti
    foreach ($reviews as $review) {
        $days_ago = (time() - strtotime($review->date)) / (60 * 60 * 24);
        if ($days_ago <= 7) {
            $html = str_replace(
                'trs-review-content',
                'trs-review-content recent-review',
                $html
            );
        }
    }
    
    return $html;
}

// Modifica i dati delle recensioni prima del salvataggio
add_filter('trs_review_data', 'custom_review_data');
function custom_review_data($review_data) {
    // Aggiungi un campo personalizzato
    $review_data['custom_field'] = 'valore_personalizzato';
    
    // Modifica il contenuto se necessario
    if (strlen($review_data['content']) > 200) {
        $review_data['content'] = substr($review_data['content'], 0, 200) . '...';
    }
    
    return $review_data;
}

// ===== ESEMPIO 24: Shortcode personalizzato =====
// Aggiungi questo nel file functions.php del tema

add_shortcode('trustpilot_reviews_compact', 'compact_reviews_shortcode');
function compact_reviews_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 3,
        'show_rating' => 'true'
    ), $atts);
    
    // Usa il shortcode originale con parametri compatti
    return do_shortcode('[trustpilot_reviews style="grid" limit="' . $atts['limit'] . '" show_rating="' . $atts['show_rating'] . '" show_date="false" show_author="false"]');
}

// Utilizzo: [trustpilot_reviews_compact limit="5"]

// ===== ESEMPIO 25: Widget personalizzato =====
// Aggiungi questo nel file functions.php del tema

class TrustpilotReviewsWidget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'trustpilot_reviews_widget',
            'Trustpilot Reviews Widget',
            array('description' => 'Mostra le recensioni Trustpilot in un widget')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $shortcode = '[trustpilot_reviews style="grid" limit="' . $instance['limit'] . '" show_rating="true" show_date="false"]';
        echo do_shortcode($shortcode);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 3;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Titolo:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">Numero recensioni:</label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('limit'); ?>" 
                   name="<?php echo $this->get_field_name('limit'); ?>" type="number" 
                   value="<?php echo esc_attr($limit); ?>" min="1" max="10">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? strip_tags($new_instance['limit']) : 3;
        return $instance;
    }
}

// Registra il widget
add_action('widgets_init', function() {
    register_widget('TrustpilotReviewsWidget');
}); 