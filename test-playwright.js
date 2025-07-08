const { chromium } = require('playwright');

async function testPlaywright() {
    let browser;
    try {
        console.log('🚀 Avvio test Playwright...');
        
        browser = await chromium.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu',
                '--disable-background-timer-throttling',
                '--disable-backgrounding-occluded-windows',
                '--disable-renderer-backgrounding'
            ]
        });

        console.log('✅ Browser avviato con successo');

        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport: { width: 1920, height: 1080 },
            locale: 'it-IT',
            timezoneId: 'Europe/Rome'
        });

        console.log('✅ Context creato con successo');

        const page = await context.newPage();
        
        // Test con una pagina semplice
        console.log('🌐 Navigazione a Google...');
        await page.goto('https://www.google.com', { waitUntil: 'networkidle' });
        
        const title = await page.title();
        console.log(`✅ Pagina caricata: ${title}`);
        
        // Test con Trustpilot
        console.log('🌐 Navigazione a Trustpilot...');
        await page.goto('https://www.trustpilot.com', { waitUntil: 'networkidle' });
        
        const trustpilotTitle = await page.title();
        console.log(`✅ Trustpilot caricato: ${trustpilotTitle}`);
        
        // Verifica che il banner cookie sia presente
        const cookieBanner = await page.$('#onetrust-consent-sdk');
        if (cookieBanner) {
            console.log('🍪 Banner cookie rilevato');
        } else {
            console.log('ℹ️ Nessun banner cookie rilevato');
        }
        
        console.log('✅ Test completato con successo!');
        
    } catch (error) {
        console.error('❌ Errore durante il test:', error.message);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
            console.log('🔒 Browser chiuso');
        }
    }
}

// Esegui il test
testPlaywright(); 