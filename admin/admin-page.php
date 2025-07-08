<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('trs_settings', array(
    'trustpilot_url' => '',
    'auto_scrape' => false,
    'scrape_interval' => 'daily',
    'display_style' => 'carousel',
    'reviews_per_page' => 6,
    'show_rating' => true,
    'show_date' => true,
    'show_author' => true
));
$reviews_count = $this->get_reviews_count();
?>

<div class="wrap">
    <h1><span class="dashicons dashicons-star-filled"></span> Trustpilot Reviews Scraper</h1>
    
    <div class="trs-admin-container">
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab nav-tab-active">Impostazioni</a>
            <a href="#scrape" class="nav-tab">Scraping Manuale</a>
            <a href="#reviews" class="nav-tab">Recensioni</a>
            <a href="#shortcode" class="nav-tab">Shortcode</a>
        </nav>
        
        <!-- Settings Tab -->
        <div id="settings" class="tab-content active">
            <form method="post" action="options.php">
                <?php settings_fields('trs_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">URL Trustpilot</th>
                        <td>
                            <input type="url" name="trs_settings[trustpilot_url]" 
                                   value="<?php echo esc_attr($settings['trustpilot_url']); ?>" 
                                   class="regular-text" placeholder="https://www.trustpilot.com/review/tua-azienda.com">
                            <p class="description">Inserisci l'URL della pagina recensioni Trustpilot della tua azienda</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Stile di Visualizzazione</th>
                        <td>
                            <select name="trs_settings[display_style]">
                                <option value="carousel" <?php selected($settings['display_style'], 'carousel'); ?>>Carosello</option>
                                <option value="gallery" <?php selected($settings['display_style'], 'gallery'); ?>>Galleria</option>
                                <option value="masonry" <?php selected($settings['display_style'], 'masonry'); ?>>Masonry</option>
                                <option value="grid" <?php selected($settings['display_style'], 'grid'); ?>>Grid</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Recensioni per Pagina</th>
                        <td>
                            <input type="number" name="trs_settings[reviews_per_page]" 
                                   value="<?php echo esc_attr($settings['reviews_per_page']); ?>" 
                                   min="1" max="50" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Mostra Elementi</th>
                        <td>
                            <label><input type="checkbox" name="trs_settings[show_rating]" 
                                         value="1" <?php checked($settings['show_rating'], true); ?>> Rating</label><br>
                            <label><input type="checkbox" name="trs_settings[show_date]" 
                                         value="1" <?php checked($settings['show_date'], true); ?>> Data</label><br>
                            <label><input type="checkbox" name="trs_settings[show_author]" 
                                         value="1" <?php checked($settings['show_author'], true); ?>> Autore</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Scraping Automatico</th>
                        <td>
                            <label><input type="checkbox" name="trs_settings[auto_scrape]" 
                                         value="1" <?php checked($settings['auto_scrape'], true); ?>> Abilita scraping automatico</label><br>
                            <select name="trs_settings[scrape_interval]">
                                <option value="hourly" <?php selected($settings['scrape_interval'], 'hourly'); ?>>Ogni ora</option>
                                <option value="daily" <?php selected($settings['scrape_interval'], 'daily'); ?>>Giornaliero</option>
                                <option value="weekly" <?php selected($settings['scrape_interval'], 'weekly'); ?>>Settimanale</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Salva Impostazioni'); ?>
            </form>
        </div>
        
        <!-- Scrape Tab -->
        <div id="scrape" class="tab-content">
            <h2>Scraping Manuale</h2>
            <p>Inserisci l'URL Trustpilot e clicca su "Scrapa Recensioni" per importare le recensioni manualmente.</p>
            
            <div class="trs-scrape-form">
                <input type="url" id="trustpilot_url" placeholder="https://www.trustpilot.com/review/tua-azienda.com" class="regular-text">
                <button type="button" id="scrape_reviews" class="button button-primary">Scrapa Recensioni</button>
                <button type="button" id="sample_reviews" class="button button-secondary">Carica Recensioni di Esempio</button>
            </div>
            
            <div id="scrape_result" class="trs-result"></div>
            <textarea id="trs-scrape-debug" style="width:100%;height:180px;font-size:12px;display:none;margin-top:10px;" readonly></textarea>
            
            <div class="trs-debug-section" style="margin-top:20px;padding:15px;background:#f9f9f9;border:1px solid #ddd;">
                <h3>Debug Log</h3>
                <p>Controlla i log di WordPress per vedere i dettagli dello scraping:</p>
                <code>wp-content/debug.log</code>
                <p>Oppure abilita il debug WordPress aggiungendo questo in wp-config.php:</p>
                <pre style="background:#fff;padding:10px;border:1px solid #ccc;">
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);</pre>
            </div>
            
            <div class="trs-stats">
                <h3>Statistiche</h3>
                <p>Recensioni salvate: <strong><?php echo $reviews_count; ?></strong></p>
            </div>
        </div>
        
        <!-- Reviews Tab -->
        <div id="reviews" class="tab-content">
            <h2>Recensioni Salvate</h2>
            <?php $this->display_reviews_table(); ?>
        </div>
        
        <!-- Shortcode Tab -->
        <div id="shortcode" class="tab-content">
            <h2>Come Utilizzare lo Shortcode</h2>
            <p>Utilizza questo shortcode per mostrare le recensioni nel tuo sito. Puoi inserirlo in pagine, post, widget o template PHP.</p>
            
            <div class="trs-shortcode-examples">
                <h3>📋 Shortcode Base</h3>
                <code>[trustpilot_reviews]</code>
                <p class="description">Mostra le recensioni con le impostazioni predefinite</p>
                
                <h3>🎨 Esempi di Utilizzo</h3>
                
                <h4>Carosello con 6 recensioni</h4>
                <code>[trustpilot_reviews style="carousel" limit="6"]</code>
                
                <h4>Galleria con solo rating e autore</h4>
                <code>[trustpilot_reviews style="gallery" show_rating="true" show_author="true" show_date="false"]</code>
                
                <h4>Layout masonry con 12 recensioni</h4>
                <code>[trustpilot_reviews style="masonry" limit="12"]</code>
                
                <h4>Grid responsive per mobile</h4>
                <code>[trustpilot_reviews style="grid" limit="4"]</code>
                
                <h4>Carosello con poche recensioni per hero section</h4>
                <code>[trustpilot_reviews style="carousel" limit="3" show_author="false"]</code>
                
                <h3>⚙️ Parametri Disponibili</h3>
                <table class="trs-params-table">
                    <thead>
                        <tr>
                            <th>Parametro</th>
                            <th>Valori</th>
                            <th>Descrizione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>style</strong></td>
                            <td>carousel, gallery, masonry, grid</td>
                            <td>Stile di visualizzazione delle recensioni</td>
                        </tr>
                        <tr>
                            <td><strong>limit</strong></td>
                            <td>1-50</td>
                            <td>Numero di recensioni da mostrare</td>
                        </tr>
                        <tr>
                            <td><strong>show_rating</strong></td>
                            <td>true/false</td>
                            <td>Mostra le stelle del rating</td>
                        </tr>
                        <tr>
                            <td><strong>show_date</strong></td>
                            <td>true/false</td>
                            <td>Mostra la data della recensione</td>
                        </tr>
                        <tr>
                            <td><strong>show_author</strong></td>
                            <td>true/false</td>
                            <td>Mostra l'autore della recensione</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>🎯 Esempi per Casi d'Uso Specifici</h3>
                
                <h4>Homepage Hero Section</h4>
                <code>[trustpilot_reviews style="carousel" limit="3" show_author="false"]</code>
                <p class="description">Perfetto per mostrare le migliori recensioni in evidenza</p>
                
                <h4>Sidebar Widget</h4>
                <code>[trustpilot_reviews style="grid" limit="3" show_date="false"]</code>
                <p class="description">Layout compatto per widget laterali</p>
                
                <h4>Pagina Dedicata Recensioni</h4>
                <code>[trustpilot_reviews style="masonry" limit="20"]</code>
                <p class="description">Mostra molte recensioni in layout a cascata</p>
                
                <h4>Footer</h4>
                <code>[trustpilot_reviews style="grid" limit="4" show_date="false"]</code>
                <p class="description">Layout semplice per il footer del sito</p>
                
                <h3>💡 Suggerimenti</h3>
                <ul>
                    <li>Usa <code>style="carousel"</code> per sezioni in evidenza</li>
                    <li>Usa <code>style="gallery"</code> per mostrare molte recensioni</li>
                    <li>Usa <code>style="masonry"</code> per layout creativi</li>
                    <li>Usa <code>style="grid"</code> per layout semplici e puliti</li>
                    <li>Imposta <code>limit="3-6"</code> per performance ottimali</li>
                    <li>Disabilita <code>show_date="false"</code> per layout più compatti</li>
                </ul>
                
                <h3>🔧 Personalizzazione Avanzata</h3>
                <p>Per personalizzazioni avanzate, puoi modificare il CSS o creare shortcode personalizzati nel file <code>functions.php</code> del tuo tema:</p>
                
                <h4>Esempio: Shortcode Personalizzato</h4>
                <pre><code>add_shortcode('reviews_compact', function($atts) {
    return do_shortcode('[trustpilot_reviews style="grid" limit="3" show_date="false"]');
});</code></pre>
                
                <h4>Utilizzo: <code>[reviews_compact]</code></h4>
                
                <h3>🔄 Personalizzare il Nome dello Shortcode</h3>
                <p>Se vuoi usare un nome diverso per lo shortcode (es. <code>[reviews]</code> invece di <code>[trustpilot_reviews]</code>), aggiungi questo codice nel file <code>functions.php</code> del tuo tema:</p>
                
                <h4>Esempio: Cambiare Nome Shortcode</h4>
                <pre><code>// Cambia il nome dello shortcode da [trustpilot_reviews] a [reviews]
add_filter('trs_custom_shortcode_name', function($shortcode_name) {
    return 'reviews';
});</code></pre>
                
                <h4>Ora puoi usare: <code>[reviews]</code></h4>
                
                <h4>Esempio: Shortcode Multipli</h4>
                <pre><code>// Crea shortcode multipli per diversi usi
add_shortcode('reviews_carousel', function($atts) {
    return do_shortcode('[trustpilot_reviews style="carousel" limit="6"]');
});

add_shortcode('reviews_gallery', function($atts) {
    return do_shortcode('[trustpilot_reviews style="gallery" limit="12"]');
});

add_shortcode('reviews_sidebar', function($atts) {
    return do_shortcode('[trustpilot_reviews style="grid" limit="3" show_date="false"]');
});</code></pre>
                
                <h4>Utilizzo:</h4>
                <ul>
                    <li><code>[reviews_carousel]</code> - Carosello di recensioni</li>
                    <li><code>[reviews_gallery]</code> - Galleria di recensioni</li>
                    <li><code>[reviews_sidebar]</code> - Recensioni per sidebar</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.trs-admin-container {
    margin-top: 20px;
}

.tab-content {
    display: none;
    padding: 20px 0;
}

.tab-content.active {
    display: block;
}

.trs-scrape-form {
    margin: 20px 0;
    padding: 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.trs-result {
    margin: 20px 0;
    padding: 15px;
    border-radius: 4px;
}

.trs-result.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.trs-result.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.trs-stats {
    margin-top: 30px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.trs-shortcode-examples code {
    display: block;
    background: #f4f4f4;
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
    font-family: monospace;
}

.trs-shortcode-examples pre {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    overflow-x: auto;
    margin: 10px 0;
}

.trs-shortcode-examples pre code {
    background: none;
    padding: 0;
    margin: 0;
    border-radius: 0;
}

.trs-params-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.trs-params-table th,
.trs-params-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.trs-params-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.trs-params-table tr:hover {
    background: #f8f9fa;
}

.trs-shortcode-examples .description {
    color: #666;
    font-style: italic;
    margin: 5px 0 15px 0;
}

.trs-shortcode-examples h3 {
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 5px;
    margin-top: 30px;
}

.trs-shortcode-examples h4 {
    color: #555;
    margin-top: 20px;
    margin-bottom: 10px;
}
</style>

<!-- Definizione trs_ajax per JS inline -->
<script>
window.trs_ajax = <?php echo json_encode(array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('trs_nonce')
)); ?>;
</script>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // Scraping AJAX
    $('#scrape_reviews').on('click', function(e){
        e.preventDefault();
        var url = $('#trustpilot_url').val();
        $('#scrape_result').html('');
        $('#trs-scrape-debug').hide().val('');
        if(!url){
            $('#scrape_result').html('<span style="color:red;">Inserisci un URL Trustpilot valido.</span>');
            return;
        }
        $('#scrape_result').html('<img src="'+trs_ajax.ajax_url.replace('admin-ajax.php','')+'../assets/bbr-loading-icon.gif'+'" style="height:20px;vertical-align:middle;" /> Scraping in corso...');
        $.ajax({
            url: trs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'trs_scrape_reviews',
                trustpilot_url: url,
                nonce: trs_ajax.nonce
            },
            dataType: 'json',
            success: function(response, status, xhr){
                if(response.success){
                    $('#scrape_result').html('<span style="color:green;">'+response.data.message+'</span>');
                }else{
                    $('#scrape_result').html('<span style="color:red;">'+(response.data ? response.data : 'Errore sconosciuto')+'</span>');
                }
                // Mostra debug verboso
                var debug = 'Risposta AJAX (status '+xhr.status+'):\n';
                debug += JSON.stringify(response, null, 2);
                if(xhr && xhr.responseText){
                    debug += '\n---\nRisposta grezza:\n'+xhr.responseText;
                }
                $('#trs-scrape-debug').val(debug).show();
            },
            error: function(xhr, status, error){
                $('#scrape_result').html('<span style="color:red;">Errore AJAX: '+error+'</span>');
                var debug = 'Errore AJAX (status '+xhr.status+'):\n';
                debug += xhr.responseText ? xhr.responseText : '';
                $('#trs-scrape-debug').val(debug).show();
            }
        });
    });
        
    $('#sample_reviews').click(function() {
        var button = $(this);
        button.prop('disabled', true).text('Caricamento...');
        $('#scrape_result').html('<div class="trs-result info">Caricamento recensioni di esempio...</div>');
        
        $.ajax({
            url: trs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'trs_load_sample_reviews',
                nonce: trs_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#scrape_result').html('<div class="trs-result success">' + response.data.message + '</div>');
                    // Ricarica la pagina dopo 2 secondi per mostrare le nuove recensioni
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $('#scrape_result').html('<div class="trs-result error">Errore: ' + response.data + '</div>');
                }
            },
            error: function() {
                $('#scrape_result').html('<div class="trs-result error">Errore di connessione</div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Carica Recensioni di Esempio');
            }
        });
    });
});
</script> 