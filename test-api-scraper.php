<?php
/**
 * Test API Scraper
 * Testa il funzionamento dell'API scraper
 */

// Simula WordPress
define('ABSPATH', dirname(__FILE__) . '/');

// Carica la classe API Scraper
require_once 'includes/class-api-scraper.php';

echo "🧪 Test API Scraper\n";
echo "===================\n\n";

// Crea istanza dello scraper
$scraper = new ApiScraper();

// Test 1: Connessione API
echo "1. Test connessione API...\n";
$connection_test = $scraper->test_connection();
if ($connection_test['success']) {
    echo "✅ " . $connection_test['message'] . "\n";
} else {
    echo "❌ " . $connection_test['message'] . "\n";
    echo "⚠️  Assicurati che l'API sia deployata su Render.com\n";
    echo "⚠️  Aggiorna l'URL dell'API in class-api-scraper.php\n\n";
    exit;
}

echo "\n";

// Test 2: Scraping test
echo "2. Test scraping...\n";
$scraping_test = $scraper->test_scraping();
if ($scraping_test['success']) {
    echo "✅ " . $scraping_test['message'] . "\n";
} else {
    echo "❌ " . $scraping_test['message'] . "\n";
}

echo "\n";

// Test 3: Scraping specifico
echo "3. Test scraping Amazon...\n";
try {
    $reviews = $scraper->scrape_reviews('https://www.trustpilot.com/review/amazon.com');
    
    if (!empty($reviews)) {
        echo "✅ Trovate " . count($reviews) . " recensioni\n";
        
        // Mostra le prime 3 recensioni
        echo "\n📋 Prime 3 recensioni:\n";
        for ($i = 0; $i < min(3, count($reviews)); $i++) {
            $review = $reviews[$i];
            echo "\n--- Recensione " . ($i + 1) . " ---\n";
            echo "Nome: " . $review['name'] . "\n";
            echo "Rating: " . $review['rating'] . "/5\n";
            echo "Titolo: " . $review['title'] . "\n";
            echo "Testo: " . substr($review['text'], 0, 100) . "...\n";
            echo "Data: " . $review['date'] . "\n";
            if (!empty($review['country'])) {
                echo "Paese: " . $review['country'] . "\n";
            }
        }
    } else {
        echo "❌ Nessuna recensione trovata\n";
    }
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
}

echo "\n";
echo "🏁 Test completato!\n"; 