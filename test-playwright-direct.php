<?php
/**
 * Test diretto della classe Playwright Scraper
 */

// Carica solo la classe Playwright
require_once 'includes/class-playwright-scraper.php';

echo "🧪 Test diretto Playwright Scraper\n";
echo "==================================\n\n";

// Test 1: Verifica classe
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

// Test 3: Verifica se Playwright è installato
echo "\n3. Test installazione Playwright...\n";
$playwright_path = dirname(__FILE__) . '/node_modules/.bin/playwright';
if (file_exists($playwright_path)) {
    echo "✅ Playwright trovato: $playwright_path\n";
} else {
    echo "❌ Playwright non trovato. Esegui: npm install\n";
    exit(1);
}

echo "\n🎯 Test completato!\n";
echo "Il plugin è pronto per l'uso.\n";
echo "Attiva il plugin in WordPress e usa il pannello di amministrazione.\n"; 