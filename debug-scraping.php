<?php
/**
 * Debug Script per Trustpilot Scraper
 * 
 * Questo script permette di testare lo scraping con output dettagliato
 */

// Carica WordPress
require_once('../../../wp-load.php');

// Verifica che il plugin sia attivo
if (!class_exists('TrustpilotReviewsScraper')) {
    die('Plugin Trustpilot Reviews Scraper non trovato');
}

// URL di test
$test_url = 'https://www.trustpilot.com/review/amazon.com';

echo "<h1>Debug Trustpilot Scraper</h1>";
echo "<p><strong>URL di test:</strong> $test_url</p>";

try {
    // Crea istanza del plugin
    $plugin = new TrustpilotReviewsScraper();
    
    echo "<h2>1. Test connessione Playwright</h2>";
    
    // Test se Playwright è installato
    $node_path = 'node';
    $playwright_path = __DIR__ . '/node_modules/.bin/playwright';
    
    if (file_exists($playwright_path)) {
        echo "<p>✅ Playwright trovato: $playwright_path</p>";
    } else {
        echo "<p>❌ Playwright non trovato in: $playwright_path</p>";
        echo "<p>Esegui: <code>npm install</code> nella directory del plugin</p>";
    }
    
    echo "<h2>2. Test scraping</h2>";
    
    // Test scraping
    $start_time = microtime(true);
    
    $reviews = $plugin->scrape_trustpilot_reviews($test_url);
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    echo "<p><strong>Tempo di esecuzione:</strong> {$execution_time} secondi</p>";
    echo "<p><strong>Recensioni trovate:</strong> " . count($reviews) . "</p>";
    
    if (!empty($reviews)) {
        echo "<h3>Prime 3 recensioni:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Autore</th><th>Rating</th><th>Titolo</th><th>Data</th></tr>";
        
        for ($i = 0; $i < min(3, count($reviews)); $i++) {
            $review = $reviews[$i];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($review['author']) . "</td>";
            echo "<td>" . $review['rating'] . " stelle</td>";
            echo "<td>" . htmlspecialchars($review['title']) . "</td>";
            echo "<td>" . $review['date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Salva le recensioni nel database
        echo "<h2>3. Salvataggio nel database</h2>";
        $plugin->save_reviews($reviews);
        echo "<p>✅ Recensioni salvate nel database</p>";
        
        // Verifica il salvataggio
        $saved_count = $plugin->get_reviews_count();
        echo "<p><strong>Recensioni nel database:</strong> $saved_count</p>";
        
    } else {
        echo "<p>❌ Nessuna recensione trovata</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Errore</h2>";
    echo "<p><strong>Messaggio:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linea:</strong> " . $e->getLine() . "</p>";
}

echo "<h2>4. Log di sistema</h2>";
echo "<p>Controlla il file di log di WordPress per dettagli aggiuntivi:</p>";
echo "<code>wp-content/debug.log</code>";

echo "<h2>5. Comandi utili</h2>";
echo "<ul>";
echo "<li><code>npm install</code> - Installa Playwright</li>";
echo "<li><code>npm run install-browsers</code> - Installa i browser</li>";
echo "<li><code>node test-playwright.js</code> - Test base Playwright</li>";
echo "<li><code>node test-pagination.js</code> - Test paginazione</li>";
echo "</ul>";

echo "<h2>6. Troubleshooting</h2>";
echo "<ul>";
echo "<li>Verifica che Node.js sia installato: <code>node --version</code></li>";
echo "<li>Verifica che npm sia installato: <code>npm --version</code></li>";
echo "<li>Controlla i permessi della directory: <code>ls -la</code></li>";
echo "<li>Verifica la connessione internet</li>";
echo "<li>Prova con un URL diverso</li>";
echo "</ul>";
?> 