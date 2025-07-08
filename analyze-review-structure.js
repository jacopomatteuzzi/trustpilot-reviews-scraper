const { chromium } = require('playwright');

async function analyzeReviewStructure() {
    let browser;
    try {
        console.log('🚀 Analisi struttura recensione Trustpilot...');
        
        browser = await chromium.launch({
            headless: false,
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

        // Aspetta il caricamento
        await page.waitForTimeout(3000);
        
        // Analizza la prima recensione
        const reviewStructure = await page.evaluate(() => {
            const reviewElement = document.querySelector('article.styles_reviewCard__meSdm');
            if (!reviewElement) {
                return { error: 'Nessuna recensione trovata' };
            }
            
            // Cerca il nome dell'utente
            const nameSelectors = [
                'button[aria-label*="Info for"]',
                '.styles_consumerInfoWrapper__6HN5O button',
                '.styles_btnWrapper__arGPQ',
                'button[aria-label*="Info"]'
            ];
            
            let name = 'Utente anonimo';
            for (const selector of nameSelectors) {
                const element = reviewElement.querySelector(selector);
                if (element) {
                    const ariaLabel = element.getAttribute('aria-label');
                    if (ariaLabel && ariaLabel.includes('Info for')) {
                        name = ariaLabel.replace('Info for ', '');
                        break;
                    }
                }
            }
            
            // Cerca il rating
            const ratingSelectors = [
                '[data-service-review-rating]',
                '.star-rating',
                '.stars',
                '[data-rating]'
            ];
            
            let rating = 0;
            for (const selector of ratingSelectors) {
                const element = reviewElement.querySelector(selector);
                if (element) {
                    const ratingAttr = element.getAttribute('data-service-review-rating');
                    if (ratingAttr) {
                        rating = parseInt(ratingAttr);
                        break;
                    }
                }
            }
            
            // Cerca il titolo
            const titleSelectors = [
                'h2',
                'h3',
                'h4',
                '.review-title',
                '[data-testid="review-title"]'
            ];
            
            let title = '';
            for (const selector of titleSelectors) {
                const element = reviewElement.querySelector(selector);
                if (element && element.textContent.trim()) {
                    title = element.textContent.trim();
                    break;
                }
            }
            
            // Cerca il testo della recensione
            const textSelectors = [
                '.styles_reviewText__q8Zhv',
                '.review-content',
                '.review-text',
                'p',
                '[data-testid="review-content"]'
            ];
            
            let text = '';
            for (const selector of textSelectors) {
                const element = reviewElement.querySelector(selector);
                if (element && element.textContent.trim()) {
                    text = element.textContent.trim();
                    break;
                }
            }
            
            // Cerca la data
            const dateSelectors = [
                'time',
                '[datetime]',
                '.review-date',
                '.date'
            ];
            
            let date = '';
            for (const selector of dateSelectors) {
                const element = reviewElement.querySelector(selector);
                if (element) {
                    date = element.getAttribute('datetime') || element.textContent.trim();
                    if (date) break;
                }
            }
            
            // Analizza tutti gli elementi figli
            const allChildren = [];
            const analyzeElement = (element, depth = 0) => {
                const children = element.children;
                for (let i = 0; i < children.length; i++) {
                    const child = children[i];
                    const childInfo = {
                        tagName: child.tagName,
                        className: child.className,
                        id: child.id,
                        dataTestId: child.getAttribute('data-testid'),
                        textContent: child.textContent.substring(0, 50),
                        depth: depth
                    };
                    allChildren.push(childInfo);
                    analyzeElement(child, depth + 1);
                }
            };
            
            analyzeElement(reviewElement);
            
            return {
                name,
                rating,
                title,
                text,
                date,
                allChildren: allChildren.slice(0, 20) // Solo i primi 20
            };
        });
        
        console.log('Struttura recensione:', JSON.stringify(reviewStructure, null, 2));
        
        console.log('✅ Analisi completata');
        
    } catch (error) {
        console.error('❌ Errore analisi:', error);
    } finally {
        if (browser) {
            await browser.close();
            console.log('🔒 Browser chiuso');
        }
    }
}

analyzeReviewStructure(); 