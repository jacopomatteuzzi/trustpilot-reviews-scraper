<?php
echo "Test caricamento classe Playwright\n";
echo "================================\n";

// Verifica se il file esiste
$file = 'includes/class-playwright-scraper.php';
if (file_exists($file)) {
    echo "✅ File trovato: $file\n";
} else {
    echo "❌ File non trovato: $file\n";
    exit(1);
}

// Prova a caricare il file
try {
    require_once $file;
    echo "✅ File caricato con successo\n";
} catch (Exception $e) {
    echo "❌ Errore caricamento: " . $e->getMessage() . "\n";
    exit(1);
}

// Prova a creare l'istanza
try {
    $scraper = new PlaywrightScraper();
    echo "✅ Classe PlaywrightScraper creata con successo\n";
} catch (Exception $e) {
    echo "❌ Errore creazione classe: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎯 Test completato con successo!\n";
?> 