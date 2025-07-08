# Trustpilot Reviews Scraper - Plugin WordPress

Plugin WordPress per lo scraping delle recensioni Trustpilot utilizzando Playwright per aggirare i blocchi e raccogliere tutte le recensioni disponibili.

## 🚀 Caratteristiche

- **Scraping completo**: Raccoglie tutte le recensioni disponibili, non solo le prime 5
- **Aggira i blocchi**: Utilizza Playwright per simulare un browser reale
- **Gestione cookie**: Gestisce automaticamente i banner cookie di Trustpilot
- **Paginazione automatica**: Naviga automaticamente tra le pagine delle recensioni
- **Interfaccia admin**: Pannello di amministrazione per gestire lo scraping
- **Shortcode**: `[trustpilot_reviews]` per visualizzare le recensioni
- **Stili multipli**: Carosello, galleria o masonry

## 📋 Requisiti

- WordPress 5.0+
- PHP 7.4+
- Node.js 16+
- Composer (opzionale, per gestione dipendenze)

## 🔧 Installazione

### 1. Installazione del Plugin

1. Carica la cartella `trustpilot-reviews-scraper` nella directory `/wp-content/plugins/` del tuo WordPress
2. Attiva il plugin dal pannello di amministrazione WordPress

### 2. Installazione di Playwright

Il plugin richiede Playwright per funzionare. Esegui questi comandi nella directory del plugin:

```bash
cd /path/to/wp-content/plugins/trustpilot-reviews-scraper

# Installa le dipendenze Node.js
npm install

# Installa i browser necessari per Playwright
npm run install-browsers
```

### 3. Test di Funzionamento

Prima di utilizzare il plugin, verifica che Playwright funzioni correttamente:

```bash
# Test di base
npm test

# Oppure esegui direttamente
node test-playwright.js
```

Se il test va a buon fine, vedrai:
```
🚀 Avvio test Playwright...
✅ Browser avviato con successo
✅ Context creato con successo
🌐 Navigazione a Google...
✅ Pagina caricata: Google
🌐 Navigazione a Trustpilot...
✅ Trustpilot caricato: Trustpilot: Reviews on Everything
🍪 Banner cookie rilevato
✅ Test completato con successo!
🔒 Browser chiuso
```

## 🎯 Utilizzo

### Pannello di Amministrazione

1. Vai su **Trustpilot Reviews** nel menu di amministrazione
2. Inserisci l'URL dell'azienda su Trustpilot (es: `https://www.trustpilot.com/review/example.com`)
3. Clicca su **Scrapa Recensioni**
4. Attendi il completamento dello scraping
5. Visualizza le recensioni raccolte

### Shortcode

Utilizza lo shortcode `[trustpilot_reviews]` nelle tue pagine o post:

```php
[trustpilot_reviews style="carousel" limit="6" show_rating="true" show_date="true" show_author="true"]
```

**Parametri disponibili:**
- `style`: `carousel`, `gallery`, `masonry`
- `limit`: numero di recensioni da mostrare
- `show_rating`: `true`/`false` per mostrare le stelle
- `show_date`: `true`/`false` per mostrare la data
- `show_author`: `true`/`false` per mostrare l'autore

## 🔧 Configurazione

### Impostazioni del Plugin

Nel pannello di amministrazione puoi configurare:

- **URL Trustpilot**: L'URL dell'azienda da monitorare
- **Stile di visualizzazione**: Carosello, galleria o masonry
- **Recensioni per pagina**: Numero di recensioni da mostrare
- **Mostra rating**: Abilita/disabilita la visualizzazione delle stelle
- **Mostra data**: Abilita/disabilita la visualizzazione della data
- **Mostra autore**: Abilita/disabilita la visualizzazione dell'autore

### Personalizzazione CSS

Il plugin include file CSS personalizzabili:

- `assets/css/trustpilot-reviews.css`: Stili principali
- `assets/css/carousel.css`: Stili per il carosello
- `assets/css/grid.css`: Stili per la galleria

## 🐛 Risoluzione Problemi

### Playwright non funziona

1. **Verifica Node.js**: Assicurati che Node.js sia installato
   ```bash
   node --version
   ```

2. **Reinstalla Playwright**:
   ```bash
   npm uninstall playwright
   npm install playwright
   npx playwright install
   ```

3. **Verifica i permessi**:
   ```bash
   chmod +x node_modules/.bin/playwright
   ```

### Errore "Nessuna recensione trovata"

1. Verifica che l'URL Trustpilot sia corretto
2. Controlla i log di errore di WordPress
3. Prova con un'azienda diversa per testare

### Errore di timeout

1. Aumenta i timeout nel file `includes/class-playwright-scraper.php`
2. Verifica la connessione internet
3. Prova a riavviare il server

### Scraping si ferma alle prime 5 recensioni

Questo è un problema comune con Trustpilot. Ecco le soluzioni:

1. **Testa la paginazione**:
   ```bash
   node test-pagination.js
   ```

2. **Usa il debug script**:
   ```bash
   php debug-scraping.php
   ```

3. **Verifica i selettori**:
   - Trustpilot cambia spesso la struttura HTML
   - I selettori potrebbero essere obsoleti
   - Controlla la console del browser per errori

4. **Soluzioni alternative**:
   - Prova con URL diversi
   - Aumenta i timeout di attesa
   - Verifica che l'azienda abbia più di 5 recensioni

5. **Debug avanzato**:
   - Abilita il debug WordPress
   - Controlla i log di Playwright
   - Usa browser non-headless per debug

## 📝 Log e Debug

Il plugin registra i log in:
- **Errori PHP**: `wp-content/debug.log` (se abilitato)
- **Log Playwright**: Console del browser durante lo scraping

Per abilitare il debug WordPress, aggiungi al `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🔒 Sicurezza

- Il plugin utilizza nonce per proteggere le richieste AJAX
- Tutti i dati vengono sanitizzati prima del salvataggio
- Le query SQL utilizzano prepared statements
- I permessi sono verificati per tutte le operazioni admin

## 📄 Licenza

GPL v2 o successiva

## 🤝 Supporto

Per problemi o domande:
1. Controlla i log di errore
2. Verifica che Playwright sia installato correttamente
3. Testa con l'URL di un'azienda diversa
4. Controlla i requisiti di sistema

## 🔄 Aggiornamenti

Per aggiornare il plugin:
1. Fai backup delle recensioni esistenti
2. Sostituisci i file del plugin
3. Esegui `npm install` per aggiornare le dipendenze
4. Testa il funzionamento

---

**Nota**: Questo plugin è per uso educativo e di test. Rispetta sempre i termini di servizio di Trustpilot e le leggi sulla privacy. 