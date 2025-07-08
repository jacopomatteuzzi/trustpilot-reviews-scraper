# 🚀 Deploy API Trustpilot Scraper su Render.com

## Panoramica
Questo progetto include un'API Flask che può essere deployata su Render.com per fare scraping delle recensioni Trustpilot. Il plugin WordPress chiamerà questa API invece di eseguire Python localmente.

## 📋 Prerequisiti
- Account su [Render.com](https://render.com) (gratuito)
- Repository Git (GitHub, GitLab, etc.)

## 🛠️ Deploy su Render.com

### 1. **Prepara il Repository**
Assicurati che questi file siano nel tuo repository:
- `api_server.py` - Server Flask
- `requirements.txt` - Dipendenze Python
- `render.yaml` - Configurazione Render (opzionale)

### 2. **Crea un Nuovo Servizio su Render**

1. Vai su [Render.com](https://render.com) e accedi
2. Clicca "New +" → "Web Service"
3. Connetti il tuo repository Git
4. Configura il servizio:
   - **Name**: `trustpilot-scraper-api`
   - **Environment**: `Python 3`
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `gunicorn api_server:app`
   - **Plan**: Free (gratuito)

### 3. **Configurazione Avanzata (Opzionale)**
Se vuoi usare `render.yaml`:
1. Assicurati che il file sia nel repository
2. Render lo rileverà automaticamente
3. Il deploy sarà configurato automaticamente

### 4. **Deploy**
1. Clicca "Create Web Service"
2. Render inizierà il deploy automaticamente
3. Aspetta che il deploy sia completato (2-3 minuti)

## 🔧 Configurazione Plugin WordPress

### 1. **Aggiorna l'URL dell'API**
Nel file `includes/class-api-scraper.php`, sostituisci:
```php
$this->api_url = 'https://your-app-name.onrender.com';
```
Con l'URL del tuo servizio Render (es: `https://trustpilot-scraper-api.onrender.com`)

### 2. **Test del Plugin**
Esegui il file di test:
```bash
php test-api-scraper.php
```

## 🌐 Endpoint API

### Health Check
```
GET https://your-app.onrender.com/health
```

### Scraping (POST)
```
POST https://your-app.onrender.com/scrape
Content-Type: application/json

{
  "url": "https://www.trustpilot.com/review/example.com",
  "max_pages": 10
}
```

### Scraping (GET - per test)
```
GET https://your-app.onrender.com/scrape?url=https://www.trustpilot.com/review/amazon.com&max_pages=5
```

## 💰 Costi
- **Render Free Plan**: Gratuito
  - 750 ore/mese di runtime
  - 512 MB RAM
  - Sleep dopo 15 minuti di inattività
  - Perfetto per uso occasionale

- **Render Paid Plan**: $7/mese
  - Sempre attivo
  - Più RAM e CPU
  - Per uso intensivo

## 🔍 Monitoraggio
- **Logs**: Disponibili nel dashboard Render
- **Metrics**: CPU, memoria, richieste
- **Uptime**: Monitoraggio automatico

## 🚨 Limitazioni Free Plan
- **Sleep**: Il servizio va in sleep dopo 15 minuti di inattività
- **Cold Start**: La prima richiesta dopo il sleep può essere lenta (10-30 secondi)
- **Timeout**: Richieste limitate a 30 secondi

## 🔧 Troubleshooting

### Errore "Connection Refused"
- Verifica che il servizio sia deployato correttamente
- Controlla i log su Render
- Assicurati che l'URL sia corretto

### Errore "Timeout"
- Il servizio potrebbe essere in sleep
- La prima richiesta dopo il sleep è più lenta
- Considera un piano a pagamento per uso intensivo

### Errore "Module Not Found"
- Verifica che `requirements.txt` sia corretto
- Controlla i log di build su Render
- Assicurati che tutte le dipendenze siano incluse

## 📈 Scaling
Per uso intensivo:
1. **Upgrade a Paid Plan**: $7/mese
2. **Auto-scaling**: Configura più istanze
3. **CDN**: Aggiungi Cloudflare per cache

## 🔒 Sicurezza
- **CORS**: Configurato per permettere richieste cross-origin
- **Rate Limiting**: Considera l'implementazione
- **API Keys**: Per uso pubblico, aggiungi autenticazione

## 📞 Supporto
- **Render Docs**: https://render.com/docs
- **Flask Docs**: https://flask.palletsprojects.com/
- **Issues**: Apri issue su GitHub per problemi specifici 