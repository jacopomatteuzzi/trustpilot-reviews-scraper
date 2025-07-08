<?php
/**
 * Test Python Scraper
 * Verifica che lo scraper Python funzioni correttamente
 */

// Carica WordPress
require_once('../../../wp-load.php');

// Verifica che il plugin sia attivo
if (!class_exists('TrustpilotReviewsScraper')) {
    die('Plugin Trustpilot Reviews Scraper non trovato');
}

echo "<h1>Test Python Scraper</h1>";

try {
    // Crea istanza del plugin
    $plugin = new TrustpilotReviewsScraper();
    
    echo "<h2>1. Test connessione Python</h2>";
    
    // Test se Python è installato
    $python_paths = [
        'python3',
        'python',
        '/usr/bin/python3',
        '/usr/local/bin/python3'
    ];
    
    $python_found = false;
    foreach ($python_paths as $path) {
        if (function_exists('exec')) {
            $output = [];
            $return_var = 0;
            @exec("which $path 2>/dev/null", $output, $return_var);
            if ($return_var === 0) {
                echo "<p>✅ Python trovato: $path</p>";
                $python_found = true;
                break;
            }
        }
    }
    
    if (!$python_found) {
        echo "<p>❌ Python non trovato</p>";
        echo "<p>Installa Python 3 sul server</p>";
    }
    
    // Test se le dipendenze sono installate
    echo "<h2>2. Test dipendenze Python</h2>";
    
    if (function_exists('exec')) {
        $check_script = "import requests, bs4, json, sys; print('OK')";
        $output = [];
        $return_var = 0;
        
        @exec("python3 -c \"$check_script\" 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<p>✅ Dipendenze Python installate</p>";
        } else {
            echo "<p>❌ Dipendenze Python mancanti</p>";
            echo "<p>Installa con: <code>pip install requests beautifulsoup4 lxml</code></p>";
        }
    } else {
        echo "<p>⚠️ Funzione exec() non disponibile</p>";
    }
    
    // Test scraping
    echo "<h2>3. Test scraping</h2>";
    
    $test_url = 'https://www.trustpilot.com/review/amazon.com';
    echo "<p><strong>URL di test:</strong> $test_url</p>";
    
    $start_time = microtime(true);
    
    try {
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
            echo "<h2>4. Salvataggio nel database</h2>";
            $plugin->save_reviews($reviews);
            echo "<p>✅ Recensioni salvate nel database</p>";
            
            // Verifica il salvataggio
            $saved_count = $plugin->get_reviews_count();
            echo "<p><strong>Recensioni nel database:</strong> $saved_count</p>";
            
        } else {
            echo "<p>❌ Nessuna recensione trovata</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Errore durante lo scraping: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Errore</h2>";
    echo "<p><strong>Messaggio:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linea:</strong> " . $e->getLine() . "</p>";
}

echo "<h2>5. Comandi utili</h2>";
echo "<ul>";
echo "<li><code>python3 --version</code> - Verifica versione Python</li>";
echo "<li><code>pip install requests beautifulsoup4 lxml</code> - Installa dipendenze</li>";
echo "<li><code>python3 scraper.py https://www.trustpilot.com/review/amazon.com</code> - Test diretto</li>";
echo "</ul>";

echo "<h2>6. Troubleshooting</h2>";
echo "<ul>";
echo "<li>Verifica che Python 3 sia installato</li>";
echo "<li>Verifica che le dipendenze siano installate</li>";
echo "<li>Controlla i permessi del file scraper.py</li>";
echo "<li>Verifica la connessione internet</li>";
echo "<li>Controlla i log di WordPress</li>";
echo "</ul>";
?> 