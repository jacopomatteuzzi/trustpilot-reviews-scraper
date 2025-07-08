#!/bin/bash

# Trustpilot Reviews Scraper - Script di Installazione
# Questo script installa automaticamente tutte le dipendenze necessarie

echo "🚀 Trustpilot Reviews Scraper - Installazione"
echo "=============================================="

# Verifica Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js non trovato. Installa Node.js prima di continuare."
    echo "   Visita: https://nodejs.org/"
    exit 1
fi

echo "✅ Node.js trovato: $(node --version)"

# Verifica npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm non trovato. Installa npm prima di continuare."
    exit 1
fi

echo "✅ npm trovato: $(npm --version)"

# Installa le dipendenze Node.js
echo ""
echo "📦 Installazione dipendenze Node.js..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ Errore durante l'installazione delle dipendenze npm"
    exit 1
fi

echo "✅ Dipendenze npm installate con successo"

# Installa i browser Playwright
echo ""
echo "🌐 Installazione browser Playwright..."
npx playwright install

if [ $? -ne 0 ]; then
    echo "❌ Errore durante l'installazione dei browser Playwright"
    exit 1
fi

echo "✅ Browser Playwright installati con successo"

# Test di funzionamento
echo ""
echo "🧪 Test di funzionamento..."
node test-playwright.js

if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 Installazione completata con successo!"
    echo ""
    echo "📋 Prossimi passi:"
    echo "1. Attiva il plugin in WordPress"
    echo "2. Vai su 'Trustpilot Reviews' nel menu admin"
    echo "3. Inserisci l'URL di un'azienda Trustpilot"
    echo "4. Clicca su 'Scrapa Recensioni'"
    echo ""
    echo "📖 Per maggiori informazioni, consulta il README.md"
else
    echo ""
    echo "❌ Test fallito. Controlla i log per dettagli."
    exit 1
fi 