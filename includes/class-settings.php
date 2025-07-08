<?php
/**
 * Classe per la gestione delle impostazioni del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class TRS_Settings {
    
    private $option_name = 'trs_settings';
    private $default_settings;
    
    public function __construct() {
        $this->set_default_settings();
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Imposta le impostazioni di default
     */
    private function set_default_settings() {
        $this->default_settings = array(
            'trustpilot_url' => '',
            'auto_scrape' => false,
            'scrape_interval' => 'daily',
            'display_style' => 'carousel',
            'reviews_per_page' => 6,
            'show_rating' => true,
            'show_date' => true,
            'show_author' => true,
            'carousel_autoplay' => false,
            'carousel_speed' => 5000,
            'masonry_columns' => 3,
            'gallery_columns' => 3,
            'enable_filters' => false,
            'enable_search' => false,
            'theme' => 'default',
            'custom_css' => '',
            'enable_cache' => true,
            'cache_duration' => 3600, // 1 ora
            'max_reviews_per_scrape' => 50,
            'scrape_timeout' => 30,
            'enable_logging' => false,
            'notification_email' => '',
            'enable_ajax_refresh' => true,
            'lazy_loading' => true,
            'responsive_breakpoints' => array(
                'mobile' => 480,
                'tablet' => 768,
                'desktop' => 1024
            )
        );
    }
    
    /**
     * Registra le impostazioni in WordPress
     */
    public function register_settings() {
        register_setting(
            'trs_settings',
            $this->option_name,
            array(
                'type' => 'array',
                'description' => 'Impostazioni Trustpilot Reviews Scraper',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => $this->default_settings
            )
        );
        
        // Sezione generale
        add_settings_section(
            'trs_general_section',
            'Impostazioni Generali',
            array($this, 'general_section_callback'),
            'trustpilot-reviews'
        );
        
        // Sezione scraping
        add_settings_section(
            'trs_scraping_section',
            'Impostazioni Scraping',
            array($this, 'scraping_section_callback'),
            'trustpilot-reviews'
        );
        
        // Sezione visualizzazione
        add_settings_section(
            'trs_display_section',
            'Impostazioni Visualizzazione',
            array($this, 'display_section_callback'),
            'trustpilot-reviews'
        );
        
        // Sezione avanzate
        add_settings_section(
            'trs_advanced_section',
            'Impostazioni Avanzate',
            array($this, 'advanced_section_callback'),
            'trustpilot-reviews'
        );
        
        // Campi generali
        add_settings_field(
            'trustpilot_url',
            'URL Trustpilot',
            array($this, 'url_field_callback'),
            'trustpilot-reviews',
            'trs_general_section'
        );
        
        add_settings_field(
            'display_style',
            'Stile di Visualizzazione',
            array($this, 'style_field_callback'),
            'trustpilot-reviews',
            'trs_general_section'
        );
        
        add_settings_field(
            'reviews_per_page',
            'Recensioni per Pagina',
            array($this, 'number_field_callback'),
            'trustpilot-reviews',
            'trs_general_section',
            array('field' => 'reviews_per_page', 'min' => 1, 'max' => 50)
        );
        
        // Campi scraping
        add_settings_field(
            'auto_scrape',
            'Scraping Automatico',
            array($this, 'checkbox_field_callback'),
            'trustpilot-reviews',
            'trs_scraping_section',
            array('field' => 'auto_scrape')
        );
        
        add_settings_field(
            'scrape_interval',
            'Intervallo Scraping',
            array($this, 'interval_field_callback'),
            'trustpilot-reviews',
            'trs_scraping_section'
        );
        
        add_settings_field(
            'max_reviews_per_scrape',
            'Max Recensioni per Scraping',
            array($this, 'number_field_callback'),
            'trustpilot-reviews',
            'trs_scraping_section',
            array('field' => 'max_reviews_per_scrape', 'min' => 10, 'max' => 200)
        );
        
        // Campi visualizzazione
        add_settings_field(
            'show_rating',
            'Mostra Rating',
            array($this, 'checkbox_field_callback'),
            'trustpilot-reviews',
            'trs_display_section',
            array('field' => 'show_rating')
        );
        
        add_settings_field(
            'show_date',
            'Mostra Data',
            array($this, 'checkbox_field_callback'),
            'trustpilot-reviews',
            'trs_display_section',
            array('field' => 'show_date')
        );
        
        add_settings_field(
            'show_author',
            'Mostra Autore',
            array($this, 'checkbox_field_callback'),
            'trustpilot-reviews',
            'trs_display_section',
            array('field' => 'show_author')
        );
        
        add_settings_field(
            'theme',
            'Tema',
            array($this, 'theme_field_callback'),
            'trustpilot-reviews',
            'trs_display_section'
        );
        
        // Campi avanzati
        add_settings_field(
            'enable_cache',
            'Abilita Cache',
            array($this, 'checkbox_field_callback'),
            'trustpilot-reviews',
            'trs_advanced_section',
            array('field' => 'enable_cache')
        );
        
        add_settings_field(
            'cache_duration',
            'Durata Cache (secondi)',
            array($this, 'number_field_callback'),
            'trustpilot-reviews',
            'trs_advanced_section',
            array('field' => 'cache_duration', 'min' => 300, 'max' => 86400)
        );
        
        add_settings_field(
            'enable_logging',
            'Abilita Logging',
            array($this, 'checkbox_field_callback'),
            'trustpilot-reviews',
            'trs_advanced_section',
            array('field' => 'enable_logging')
        );
        
        add_settings_field(
            'custom_css',
            'CSS Personalizzato',
            array($this, 'textarea_field_callback'),
            'trustpilot-reviews',
            'trs_advanced_section',
            array('field' => 'custom_css', 'rows' => 10, 'cols' => 50)
        );
    }
    
    /**
     * Callback per la sezione generale
     */
    public function general_section_callback() {
        echo '<p>Configura le impostazioni generali del plugin.</p>';
    }
    
    /**
     * Callback per la sezione scraping
     */
    public function scraping_section_callback() {
        echo '<p>Configura le impostazioni per lo scraping delle recensioni.</p>';
    }
    
    /**
     * Callback per la sezione visualizzazione
     */
    public function display_section_callback() {
        echo '<p>Configura come vengono visualizzate le recensioni.</p>';
    }
    
    /**
     * Callback per la sezione avanzate
     */
    public function advanced_section_callback() {
        echo '<p>Impostazioni avanzate per sviluppatori.</p>';
    }
    
    /**
     * Callback per campo URL
     */
    public function url_field_callback() {
        $settings = $this->get_settings();
        $value = isset($settings['trustpilot_url']) ? $settings['trustpilot_url'] : '';
        ?>
        <input type="url" name="<?php echo $this->option_name; ?>[trustpilot_url]" 
               value="<?php echo esc_attr($value); ?>" class="regular-text" 
               placeholder="https://www.trustpilot.com/review/tua-azienda.com">
        <p class="description">Inserisci l'URL della pagina recensioni Trustpilot della tua azienda</p>
        <?php
    }
    
    /**
     * Callback per campo stile
     */
    public function style_field_callback() {
        $settings = $this->get_settings();
        $value = isset($settings['display_style']) ? $settings['display_style'] : 'carousel';
        ?>
        <select name="<?php echo $this->option_name; ?>[display_style]">
            <option value="carousel" <?php selected($value, 'carousel'); ?>>Carosello</option>
            <option value="gallery" <?php selected($value, 'gallery'); ?>>Galleria</option>
            <option value="masonry" <?php selected($value, 'masonry'); ?>>Masonry</option>
            <option value="grid" <?php selected($value, 'grid'); ?>>Grid</option>
        </select>
        <p class="description">Scegli come visualizzare le recensioni</p>
        <?php
    }
    
    /**
     * Callback per campo numero
     */
    public function number_field_callback($args) {
        $settings = $this->get_settings();
        $field = $args['field'];
        $value = isset($settings[$field]) ? $settings[$field] : $this->default_settings[$field];
        $min = isset($args['min']) ? $args['min'] : 1;
        $max = isset($args['max']) ? $args['max'] : 100;
        ?>
        <input type="number" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]" 
               value="<?php echo esc_attr($value); ?>" min="<?php echo $min; ?>" 
               max="<?php echo $max; ?>" class="small-text">
        <?php
    }
    
    /**
     * Callback per campo checkbox
     */
    public function checkbox_field_callback($args) {
        $settings = $this->get_settings();
        $field = $args['field'];
        $value = isset($settings[$field]) ? $settings[$field] : $this->default_settings[$field];
        ?>
        <label>
            <input type="checkbox" name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]" 
                   value="1" <?php checked($value, true); ?>>
            Abilita questa opzione
        </label>
        <?php
    }
    
    /**
     * Callback per campo intervallo
     */
    public function interval_field_callback() {
        $settings = $this->get_settings();
        $value = isset($settings['scrape_interval']) ? $settings['scrape_interval'] : 'daily';
        ?>
        <select name="<?php echo $this->option_name; ?>[scrape_interval]">
            <option value="hourly" <?php selected($value, 'hourly'); ?>>Ogni ora</option>
            <option value="daily" <?php selected($value, 'daily'); ?>>Giornaliero</option>
            <option value="weekly" <?php selected($value, 'weekly'); ?>>Settimanale</option>
        </select>
        <p class="description">Frequenza dello scraping automatico</p>
        <?php
    }
    
    /**
     * Callback per campo tema
     */
    public function theme_field_callback() {
        $settings = $this->get_settings();
        $value = isset($settings['theme']) ? $settings['theme'] : 'default';
        ?>
        <select name="<?php echo $this->option_name; ?>[theme]">
            <option value="default" <?php selected($value, 'default'); ?>>Default</option>
            <option value="dark" <?php selected($value, 'dark'); ?>>Dark</option>
            <option value="minimal" <?php selected($value, 'minimal'); ?>>Minimal</option>
            <option value="colored" <?php selected($value, 'colored'); ?>>Colored</option>
        </select>
        <p class="description">Scegli il tema per le recensioni</p>
        <?php
    }
    
    /**
     * Callback per campo textarea
     */
    public function textarea_field_callback($args) {
        $settings = $this->get_settings();
        $field = $args['field'];
        $value = isset($settings[$field]) ? $settings[$field] : '';
        $rows = isset($args['rows']) ? $args['rows'] : 5;
        $cols = isset($args['cols']) ? $args['cols'] : 40;
        ?>
        <textarea name="<?php echo $this->option_name; ?>[<?php echo $field; ?>]" 
                  rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" 
                  class="large-text code"><?php echo esc_textarea($value); ?></textarea>
        <p class="description">Inserisci CSS personalizzato per modificare l'aspetto delle recensioni</p>
        <?php
    }
    
    /**
     * Sanitizza le impostazioni
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // URL Trustpilot
        if (isset($input['trustpilot_url'])) {
            $sanitized['trustpilot_url'] = esc_url_raw($input['trustpilot_url']);
        }
        
        // Checkbox
        $checkbox_fields = array('auto_scrape', 'show_rating', 'show_date', 'show_author', 
                               'enable_cache', 'enable_logging', 'carousel_autoplay', 
                               'enable_filters', 'enable_search', 'enable_ajax_refresh', 'lazy_loading');
        
        foreach ($checkbox_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? true : false;
        }
        
        // Numeri
        $number_fields = array('reviews_per_page', 'max_reviews_per_scrape', 'cache_duration', 
                             'carousel_speed', 'masonry_columns', 'gallery_columns', 'scrape_timeout');
        
        foreach ($number_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = intval($input[$field]);
            }
        }
        
        // Select
        $select_fields = array('display_style', 'scrape_interval', 'theme');
        
        foreach ($select_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }
        
        // Textarea
        if (isset($input['custom_css'])) {
            $sanitized['custom_css'] = wp_strip_all_tags($input['custom_css']);
        }
        
        // Email
        if (isset($input['notification_email'])) {
            $sanitized['notification_email'] = sanitize_email($input['notification_email']);
        }
        
        return $sanitized;
    }
    
    /**
     * Ottiene le impostazioni
     */
    public function get_settings() {
        $settings = get_option($this->option_name, array());
        return wp_parse_args($settings, $this->default_settings);
    }
    
    /**
     * Ottiene una singola impostazione
     */
    public function get_setting($key, $default = null) {
        $settings = $this->get_settings();
        
        if ($default === null && isset($this->default_settings[$key])) {
            $default = $this->default_settings[$key];
        }
        
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    /**
     * Aggiorna le impostazioni
     */
    public function update_settings($new_settings) {
        $current_settings = $this->get_settings();
        $updated_settings = wp_parse_args($new_settings, $current_settings);
        
        return update_option($this->option_name, $updated_settings);
    }
    
    /**
     * Resetta le impostazioni ai valori di default
     */
    public function reset_settings() {
        return update_option($this->option_name, $this->default_settings);
    }
    
    /**
     * Verifica se le impostazioni sono valide
     */
    public function validate_settings($settings) {
        $errors = array();
        
        // Verifica URL Trustpilot
        if (!empty($settings['trustpilot_url']) && !filter_var($settings['trustpilot_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'URL Trustpilot non valido';
        }
        
        // Verifica numeri
        $number_fields = array('reviews_per_page', 'max_reviews_per_scrape', 'cache_duration');
        foreach ($number_fields as $field) {
            if (isset($settings[$field]) && (!is_numeric($settings[$field]) || $settings[$field] < 1)) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' deve essere un numero positivo';
            }
        }
        
        return $errors;
    }
} 