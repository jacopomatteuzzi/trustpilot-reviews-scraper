<?php
/**
 * Plugin Name: Trustpilot Reviews Scraper
 * Description: Scraper per le recensioni Trustpilot con visualizzazione a carosello, galleria o masonry
 * Version: 1.0.0
 * Author: Il tuo nome
 * License: GPL v2 or later
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Definizioni costanti
define('TRS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TRS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TRS_VERSION', '1.0.0');

class TrustpilotReviewsScraper {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_trs_scrape_reviews', array($this, 'ajax_scrape_reviews'));
        add_action('wp_ajax_trs_load_sample_reviews', array($this, 'ajax_load_sample_reviews'));
        add_action('wp_ajax_nopriv_trs_scrape_reviews', array($this, 'ajax_scrape_reviews'));
        add_shortcode('trustpilot_reviews', array($this, 'shortcode_handler'));
        
        // Permetti di personalizzare il nome dello shortcode
        $custom_shortcode = apply_filters('trs_custom_shortcode_name', 'trustpilot_reviews');
        if ($custom_shortcode !== 'trustpilot_reviews') {
            add_shortcode($custom_shortcode, array($this, 'shortcode_handler'));
        }
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Carica la classe API Scraper
        require_once TRS_PLUGIN_PATH . 'includes/class-api-scraper.php';
    }
    
    public function init() {
        // Inizializzazione del plugin
    }
    
    public function register_settings() {
        register_setting(
            'trs_settings', // Option group
            'trs_settings', // Option name
            array(
                'type' => 'array',
                'description' => 'Impostazioni Trustpilot Reviews Scraper',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array(
                    'trustpilot_url' => '',
                    'auto_scrape' => false,
                    'scrape_interval' => 'daily',
                    'display_style' => 'carousel',
                    'reviews_per_page' => 6,
                    'show_rating' => true,
                    'show_date' => true,
                    'show_author' => true
                )
            )
        );
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // URL Trustpilot
        if (isset($input['trustpilot_url'])) {
            $sanitized['trustpilot_url'] = esc_url_raw($input['trustpilot_url']);
        }
        
        // Checkbox
        $checkbox_fields = array('auto_scrape', 'show_rating', 'show_date', 'show_author');
        foreach ($checkbox_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? true : false;
        }
        
        // Numeri
        if (isset($input['reviews_per_page'])) {
            $sanitized['reviews_per_page'] = intval($input['reviews_per_page']);
        }
        
        // Select
        if (isset($input['display_style'])) {
            $sanitized['display_style'] = sanitize_text_field($input['display_style']);
        }
        
        if (isset($input['scrape_interval'])) {
            $sanitized['scrape_interval'] = sanitize_text_field($input['scrape_interval']);
        }
        
        return $sanitized;
    }
    
    public function activate() {
        // Crea tabella per le recensioni
        $this->create_reviews_table();
        
        // Le impostazioni vengono registrate automaticamente tramite register_settings()
        // Non serve più aggiungere le impostazioni di default qui
    }
    
    public function deactivate() {
        // Pulizia se necessario
    }
    
    private function create_reviews_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'trs_reviews';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            review_id varchar(255) NOT NULL,
            author varchar(255) NOT NULL,
            rating int(1) NOT NULL,
            title text,
            content longtext,
            date datetime NOT NULL,
            helpful_votes int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY review_id (review_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Trustpilot Reviews',
            'Trustpilot Reviews',
            'manage_options',
            'trustpilot-reviews',
            array($this, 'admin_page'),
            'dashicons-star-filled',
            30
        );
    }
    
    public function admin_page() {
        include TRS_PLUGIN_PATH . 'admin/admin-page.php';
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('trs-styles', TRS_PLUGIN_URL . 'assets/css/trustpilot-reviews.css', array(), TRS_VERSION);
        wp_enqueue_script('trs-script', TRS_PLUGIN_URL . 'assets/js/trustpilot-reviews.js', array('jquery'), TRS_VERSION, true);
        
        wp_localize_script('trs-script', 'trs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('trs_nonce')
        ));
    }
    
    public function ajax_scrape_reviews() {
        check_ajax_referer('trs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Non autorizzato');
        }
        
        $trustpilot_url = sanitize_text_field($_POST['trustpilot_url']);
        
        if (empty($trustpilot_url)) {
            wp_send_json_error('URL Trustpilot richiesto');
        }
        
        // Verifica che l'URL sia valido
        if (!filter_var($trustpilot_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error('URL Trustpilot non valido');
        }
        
        // Verifica che sia un URL Trustpilot
        if (strpos($trustpilot_url, 'trustpilot.com') === false) {
            wp_send_json_error('URL deve essere di Trustpilot');
        }
        
        try {
            $reviews = $this->scrape_trustpilot_reviews($trustpilot_url);
            
            if (empty($reviews)) {
                wp_send_json_error('Nessuna recensione trovata. Verifica l\'URL o prova più tardi.');
            }
            
            $this->save_reviews($reviews);
            
            wp_send_json_success(array(
                'message' => sprintf('Scrapate %d recensioni con successo', count($reviews)),
                'count' => count($reviews)
            ));
            
        } catch (Exception $e) {
            error_log('Trustpilot Scraper - Exception: ' . $e->getMessage());
            wp_send_json_error('Errore durante lo scraping: ' . $e->getMessage());
        }
    }
    
    /**
     * Handler AJAX per caricare recensioni di esempio
     */
    public function ajax_load_sample_reviews() {
        check_ajax_referer('trs_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Non autorizzato');
        }
        
        try {
            $scraper = new TrustpilotScraper();
            $reviews = $scraper->get_sample_reviews();
            
            if (empty($reviews)) {
                wp_send_json_error('Nessuna recensione di esempio disponibile');
            }
            
            $this->save_reviews($reviews);
            
            wp_send_json_success(array(
                'message' => sprintf('Caricate %d recensioni di esempio con successo', count($reviews)),
                'count' => count($reviews)
            ));
            
        } catch (Exception $e) {
            error_log('Trustpilot Scraper - Exception: ' . $e->getMessage());
            wp_send_json_error('Errore durante il caricamento: ' . $e->getMessage());
        }
    }
    
    private function scrape_trustpilot_reviews($url) {
        try {
            // Usa API Scraper
            $scraper = new ApiScraper();
            $reviews = $scraper->scrape_reviews($url);
            
            if (empty($reviews)) {
                throw new Exception('Nessuna recensione trovata. Verifica che l\'URL sia corretto e che l\'azienda abbia recensioni su Trustpilot.');
            }
            
            // Log del numero di recensioni trovate
            error_log('Trustpilot Scraper - Recensioni trovate: ' . count($reviews));
            
            // Converti il formato delle recensioni
            $formatted_reviews = array();
            foreach ($reviews as $review) {
                $formatted_reviews[] = array(
                    'review_id' => 'api_' . md5($review['name'] . $review['date']),
                    'author' => $review['name'],
                    'rating' => intval($review['rating']),
                    'title' => $review['title'],
                    'content' => $review['text'],
                    'date' => $this->parse_date($review['date']),
                    'helpful_votes' => 0,
                    'country' => isset($review['country']) ? $review['country'] : ''
                );
            }
            
            error_log('Trustpilot Scraper - Recensioni formattate: ' . count($formatted_reviews));
            return $formatted_reviews;
            
        } catch (Exception $e) {
            error_log('Trustpilot Scraper - Exception: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Converte la data nel formato corretto
     */
    private function parse_date($date_string) {
        if (empty($date_string)) {
            return current_time('mysql');
        }
        
        // Prova diversi formati di data
        $formats = array(
            'Y-m-d\TH:i:s.v\Z',
            'Y-m-d\TH:i:s\Z',
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y',
            'm/d/Y'
        );
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $date_string);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }
        
        // Se non riesce a parsare, usa la data corrente
        return current_time('mysql');
    }
    
    private function save_reviews($reviews) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'trs_reviews';
        
        foreach ($reviews as $review) {
            $wpdb->replace(
                $table_name,
                array(
                    'review_id' => $review['review_id'],
                    'author' => $review['author'],
                    'rating' => $review['rating'],
                    'title' => $review['title'],
                    'content' => $review['content'],
                    'date' => $review['date'],
                    'helpful_votes' => $review['helpful_votes']
                ),
                array('%s', '%s', '%d', '%s', '%s', '%s', '%d')
            );
        }
    }
    
    public function shortcode_handler($atts) {
        $settings = get_option('trs_settings', array(
            'display_style' => 'carousel',
            'reviews_per_page' => 6,
            'show_rating' => true,
            'show_date' => true,
            'show_author' => true
        ));
        
        $atts = shortcode_atts(array(
            'style' => $settings['display_style'],
            'limit' => $settings['reviews_per_page'],
            'show_rating' => $settings['show_rating'],
            'show_date' => $settings['show_date'],
            'show_author' => $settings['show_author']
        ), $atts);
        
        $reviews = $this->get_reviews($atts['limit']);
        
        if (empty($reviews)) {
            return '<p>Nessuna recensione trovata.</p>';
        }
        
        ob_start();
        include TRS_PLUGIN_PATH . 'templates/reviews-display.php';
        return ob_get_clean();
    }
    
    private function get_reviews($limit = 1000) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'trs_reviews';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY date DESC LIMIT %d",
                $limit
            )
        );
    }
    
    private function get_reviews_count() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'trs_reviews';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    public function display_reviews_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'trs_reviews';
        $reviews = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date DESC LIMIT 20");
        
        if (empty($reviews)) {
            echo '<p>Nessuna recensione trovata.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>Autore</th>';
        echo '<th>Rating</th>';
        echo '<th>Titolo</th>';
        echo '<th>Data</th>';
        echo '<th>Azioni</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        foreach ($reviews as $review) {
            echo '<tr>';
            echo '<td>' . esc_html($review->author) . '</td>';
            echo '<td>';
            for ($i = 1; $i <= 5; $i++) {
                echo '<span class="trs-star ' . ($i <= $review->rating ? 'filled' : '') . '">★</span>';
            }
            echo '</td>';
            echo '<td>' . esc_html($review->title) . '</td>';
            echo '<td>' . date_i18n(get_option('date_format'), strtotime($review->date)) . '</td>';
            echo '<td><button class="button button-small" onclick="viewReview(' . $review->id . ')">Visualizza</button></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
}

// Inizializza il plugin
new TrustpilotReviewsScraper(); 