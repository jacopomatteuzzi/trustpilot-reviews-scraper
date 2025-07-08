<?php
/**
 * Test Script per Trustpilot Reviews Scraper
 * Questo file testa lo scraping delle recensioni direttamente
 */

// Simula l'ambiente WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Carica il plugin
require_once 'trustpilot-reviews-scraper.php';
require_once 'includes/class-playwright-scraper.php';

echo "🧪 Test Trustpilot Reviews Scraper\n";
echo "==================================\n\n";

// Test 1: Verifica classe Playwright
echo "1. Test classe Playwright...\n";
try {
    $scraper = new PlaywrightScraper();
    echo "✅ Classe Playwright caricata correttamente\n";
} catch (Exception $e) {
    echo "❌ Errore caricamento classe: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Verifica Node.js
echo "\n2. Test Node.js...\n";
$node_path = $scraper->find_node_path();
echo "✅ Percorso Node.js: $node_path\n";

// Test 3: Test scraping con URL di esempio
echo "\n3. Test scraping...\n";
$test_url = "https://www.trustpilot.com/review/amazon.com";

echo "URL di test: $test_url\n";
echo "⚠️  Questo test può richiedere alcuni minuti...\n\n";

try {
    $start_time = microtime(true);
    
    $result = $scraper->scrape_reviews($test_url);
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    if ($result && isset($result['success']) && $result['success']) {
        $reviews_count = count($result['reviews']);
        echo "✅ Scraping completato con successo!\n";
        echo "📊 Statistiche:\n";
        echo "   - Tempo di esecuzione: {$execution_time}s\n";
        echo "   - Recensioni trovate: $reviews_count\n";
        
        if ($reviews_count > 0) {
            echo "\n📝 Esempio recensione:\n";
            $sample_review = $result['reviews'][0];
            echo "   - Nome: " . $sample_review['name'] . "\n";
            echo "   - Rating: " . $sample_review['rating'] . "/5\n";
            echo "   - Titolo: " . substr($sample_review['title'], 0, 50) . "...\n";
            echo "   - Data: " . $sample_review['date'] . "\n";
        }
        
    } else {
        echo "❌ Scraping fallito\n";
        if (isset($result['error'])) {
            echo "Errore: " . $result['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Errore durante lo scraping: " . $e->getMessage() . "\n";
}

echo "\n🎯 Test completato!\n";
echo "Se il test è andato a buon fine, il plugin è pronto per l'uso.\n";
echo "Attiva il plugin in WordPress e usa il pannello di amministrazione.\n"; 