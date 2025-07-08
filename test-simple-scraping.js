const { chromium } = require('playwright');

async function testSimpleScraping() {
    let browser;
    try {
        console.log('🚀 Test scraping semplice Trustpilot...');
        
        browser = await chromium.launch({
            headless: false, // Per vedere cosa succede
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage'
            ]
        });

        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport: { width: 1920, height: 1080 },
            locale: 'it-IT',
            timezoneId: 'Europe/Rome'
        });

        const page = await context.newPage();
        
        // Test con un'azienda reale
        const testUrl = 'https://www.trustpilot.com/review/amazon.com';
        console.log('Navigazione a:', testUrl);
        
        await page.goto(testUrl, { waitUntil: 'networkidle' });
        await page.waitForTimeout(5000);

        // Gestisci banner cookie
        try {
            const cookieBanner = await page.$('#onetrust-consent-sdk');
            if (cookieBanner) {
                console.log('Banner cookie rilevato');
                const acceptButton = await page.$('button[id*="accept"], button[class*="accept"]');
                if (acceptButton) {
                    await acceptButton.click();
                    await page.waitForTimeout(2000);
                }
            }
        } catch (error) {
            console.log('Errore banner cookie:', error.message);
        }

        console.log('\n--- Analisi pagina ---');
        
        // Aspetta un po' per il caricamento
        await page.waitForTimeout(3000);
        
        // Prova diversi selettori per le recensioni
        const selectors = [
            '[data-service-review-card-uid]',
            '.styles_reviewCard__hcAvl',
            '.review-card',
            '[data-testid="review-card"]',
            'article[data-service-review-card-uid]',
            '.review-card__container',
            '[data-testid="review"]',
            '.review-item',
            '.review',
            'article',
            'div[class*="review"]',
            'div[class*="card"]'
        ];
        
        for (const selector of selectors) {
            try {
                const count = await page.evaluate((sel) => {
                    const elements = document.querySelectorAll(sel);
                    return elements.length;
                }, selector);
                
                if (count > 0) {
                    console.log(`✅ Selettore "${selector}": ${count} elementi trovati`);
                    
                    // Se troviamo elementi, analizziamo il primo
                    if (count > 0) {
                        const firstElement = await page.evaluate((sel) => {
                            const element = document.querySelector(sel);
                            if (element) {
                                return {
                                    tagName: element.tagName,
                                    className: element.className,
                                    id: element.id,
                                    dataTestId: element.getAttribute('data-testid'),
                                    innerHTML: element.innerHTML.substring(0, 200) + '...'
                                };
                            }
                            return null;
                        }, selector);
                        
                        if (firstElement) {
                            console.log('Primo elemento trovato:', firstElement);
                        }
                    }
                } else {
                    console.log(`❌ Selettore "${selector}": 0 elementi`);
                }
            } catch (error) {
                console.log(`❌ Errore con selettore "${selector}":`, error.message);
            }
        }
        
        // Cerca elementi con "review" nel nome della classe
        console.log('\n--- Cerca elementi con "review" ---');
        const reviewElements = await page.evaluate(() => {
            const allElements = document.querySelectorAll('*');
            const reviews = [];
            
            for (const element of allElements) {
                const className = element.className;
                if (className && typeof className === 'string' && className.includes('review')) {
                    reviews.push({
                        tagName: element.tagName,
                        className: className,
                        id: element.id,
                        dataTestId: element.getAttribute('data-testid')
                    });
                }
            }
            
            return reviews.slice(0, 10); // Solo i primi 10
        });
        
        console.log('Elementi con "review" nel nome:', reviewElements);
        
        // Cerca elementi con "card" nel nome della classe
        console.log('\n--- Cerca elementi con "card" ---');
        const cardElements = await page.evaluate(() => {
            const allElements = document.querySelectorAll('*');
            const cards = [];
            
            for (const element of allElements) {
                const className = element.className;
                if (className && typeof className === 'string' && className.includes('card')) {
                    cards.push({
                        tagName: element.tagName,
                        className: className,
                        id: element.id,
                        dataTestId: element.getAttribute('data-testid')
                    });
                }
            }
            
            return cards.slice(0, 10); // Solo i primi 10
        });
        
        console.log('Elementi con "card" nel nome:', cardElements);
        
        console.log('✅ Test completato');
        
    } catch (error) {
        console.error('❌ Errore test:', error);
    } finally {
        if (browser) {
            await browser.close();
            console.log('🔒 Browser chiuso');
        }
    }
}

testSimpleScraping(); 