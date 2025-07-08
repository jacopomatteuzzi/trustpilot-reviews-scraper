const { chromium } = require('playwright');

async function testPagination() {
    let browser;
    try {
        console.log('🚀 Test paginazione Trustpilot...');
        
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
        
        // Test con un'azienda reale (esempio)
        const testUrl = 'https://www.trustpilot.com/review/amazon.com';
        console.log('Navigazione a:', testUrl);
        
        await page.goto(testUrl, { waitUntil: 'networkidle' });
        await page.waitForTimeout(3000);

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

        // Test paginazione
        for (let pageNum = 1; pageNum <= 3; pageNum++) {
            console.log(`\n--- Pagina ${pageNum} ---`);
            
            // Aspetta recensioni con selettori aggiornati
            try {
                await page.waitForSelector('[data-service-review-card-uid], .styles_reviewCard__hcAvl, .review-card, [data-testid="review-card"], article[data-service-review-card-uid], .review-card__container', { timeout: 10000 });
            } catch (error) {
                console.log('Timeout attesa recensioni');
            }
            
            // Conta recensioni con selettori aggiornati
            const reviewCount = await page.evaluate(() => {
                const selectors = [
                    '[data-service-review-card-uid]',
                    '.styles_reviewCard__hcAvl',
                    '.review-card',
                    '[data-testid="review-card"]',
                    'article[data-service-review-card-uid]',
                    '.review-card__container',
                    '[data-testid="review"]',
                    '.review-item'
                ];
                
                for (const selector of selectors) {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length > 0) {
                        console.log(`Trovati ${elements.length} elementi con selettore: ${selector}`);
                        return elements.length;
                    }
                }
                return 0;
            });
            
            console.log(`Recensioni trovate: ${reviewCount}`);
            
            // Debug: stampa tutti gli elementi che potrebbero essere recensioni
            if (reviewCount === 0) {
                console.log('Debug: cercando elementi recensione...');
                const debugElements = await page.evaluate(() => {
                    const allElements = document.querySelectorAll('*');
                    const potentialReviews = [];
                    
                    for (const element of allElements) {
                        const className = element.className;
                        const id = element.id;
                        const dataTestId = element.getAttribute('data-testid');
                        
                        if (className && typeof className === 'string' && (className.includes('review') || className.includes('card'))) {
                            potentialReviews.push({
                                tag: element.tagName,
                                className: className,
                                id: id,
                                dataTestId: dataTestId
                            });
                        }
                    }
                    
                    return potentialReviews.slice(0, 10); // Solo i primi 10
                });
                
                console.log('Elementi potenziali trovati:', debugElements);
            }
            
            // Cerca pulsante next
            const nextSelectors = [
                'a[aria-label="Next page"]',
                'a[aria-label="Next"]',
                'a[aria-label="Pagina successiva"]',
                'button[aria-label="Next"]',
                '[data-testid="pagination-next"]',
                '.pagination__next',
                'a[data-testid="pagination-next"]'
            ];
            
            let nextBtn = null;
            for (const selector of nextSelectors) {
                nextBtn = await page.$(selector);
                if (nextBtn) {
                    console.log(`Pulsante next trovato: ${selector}`);
                    break;
                }
            }
            
            if (nextBtn) {
                const isVisible = await nextBtn.isVisible();
                const isEnabled = await nextBtn.isEnabled();
                console.log(`Pulsante visibile: ${isVisible}, abilitato: ${isEnabled}`);
                
                if (isVisible && isEnabled) {
                    await nextBtn.click();
                    await page.waitForTimeout(3000);
                } else {
                    console.log('Pulsante non cliccabile');
                    break;
                }
            } else {
                console.log('Nessun pulsante next trovato');
                break;
            }
        }
        
        console.log('✅ Test paginazione completato');
        
    } catch (error) {
        console.error('❌ Errore test paginazione:', error);
    } finally {
        if (browser) {
            await browser.close();
            console.log('🔒 Browser chiuso');
        }
    }
}

testPagination(); 