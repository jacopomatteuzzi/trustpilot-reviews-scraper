<?php
/**
 * Playwright Scraper Class
 * Gestisce lo scraping delle recensioni Trustpilot usando Playwright
 */

if (!defined('ABSPATH')) {
    exit;
}

class PlaywrightScraper {
    
    private $playwright_path;
    private $node_path;
    
    public function __construct() {
        // Gestisce sia l'ambiente WordPress che i test
        if (function_exists('plugin_dir_path')) {
            $this->playwright_path = plugin_dir_path(__FILE__) . '../node_modules/.bin/playwright';
        } else {
            $this->playwright_path = dirname(__FILE__) . '/../node_modules/.bin/playwright';
        }
        $this->node_path = $this->find_node_path();
    }
    
    /**
     * Trova il percorso di Node.js
     */
    private function find_node_path() {
        $possible_paths = [
            '/usr/local/bin/node',
            '/usr/bin/node',
            '/opt/homebrew/bin/node',
            'node'
        ];
        
        foreach ($possible_paths as $path) {
            if (is_executable($path) || $this->is_command_available($path)) {
                return $path;
            }
        }
        
        return 'node';
    }
    
    /**
     * Verifica se un comando è disponibile
     */
    private function is_command_available($command) {
        $output = [];
        $return_var = 0;
        exec("which $command 2>/dev/null", $output, $return_var);
        return $return_var === 0;
    }
    
    /**
     * Scrapa le recensioni usando Playwright
     */
    public function scrape_reviews($company_url) {
        try {
            // Crea uno script temporaneo per Playwright
            $script_content = $this->generate_playwright_script($company_url);
            $script_file = $this->create_temp_script($script_content);
            
            // Esegui Playwright
            $result = $this->execute_playwright($script_file);
            
            // Pulisci il file temporaneo
            unlink($script_file);
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Playwright Scraper - Errore: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera lo script Playwright
     */
    private function generate_playwright_script($company_url) {
        return "
const { chromium } = require('playwright');

(async () => {
    let browser;
    try {
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

        const context = await browser.newContext({
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport: { width: 1920, height: 1080 },
            locale: 'it-IT',
            timezoneId: 'Europe/Rome'
        });

        const page = await context.newPage();
        
        // Vai alla pagina principale dell'azienda
        console.log('Navigazione a:', '$company_url');
        await page.goto('$company_url', { waitUntil: 'networkidle' });
        await page.waitForTimeout(3000);

        // Gestisci il banner cookie OneTrust se presente
        try {
            const cookieBanner = await page.$('#onetrust-consent-sdk');
            if (cookieBanner) {
                console.log('Banner cookie rilevato, gestisco...');
                const acceptButton = await page.$('button[id*=\"accept\"], button[class*=\"accept\"], button:contains(\"Accept\"), button:contains(\"Accetta\")');
                if (acceptButton) {
                    await acceptButton.click();
                    await page.waitForTimeout(2000);
                    console.log('Banner cookie gestito');
                }
            }
        } catch (error) {
            console.log('Errore gestione banner cookie:', error.message);
        }

        // Vai alla sezione recensioni se non siamo già lì
        const currentUrl = page.url();
        if (!currentUrl.includes('/review/')) {
            console.log('Navigazione alla sezione recensioni...');
            const reviewsLink = await page.$('a[href*=\"/review/\"]');
            if (reviewsLink) {
                await reviewsLink.click();
                await page.waitForTimeout(3000);
            }
        }

        let allReviews = [];
        let pageNum = 1;
        let hasNext = true;
        const maxPages = 20; // Aumentato il limite

        while (hasNext && pageNum <= maxPages) {
            console.log(\`---\n[Pagina \${pageNum}] Inizio scraping pagina...\`);
            
            // Attendi che le recensioni siano caricate con timeout più lungo
            try {
                await page.waitForSelector('article.styles_reviewCard__meSdm, [data-service-review-card-uid], .styles_reviewCard__hcAvl, .review-card, [data-testid=\"review-card\"], article[data-service-review-card-uid], .review-card__container, [data-testid=\"review\"]', { timeout: 20000 });
                await page.waitForTimeout(3000); // Aumentato il tempo di attesa
            } catch (error) {
                console.log('Timeout attesa recensioni, continuo...');
            }

            // Scroll per caricare tutte le recensioni della pagina
            console.log('Scroll per caricare tutte le recensioni...');
            await page.evaluate(() => {
                return new Promise((resolve) => {
                    let totalHeight = 0;
                    const distance = 100;
                    const timer = setInterval(() => {
                        const scrollHeight = document.body.scrollHeight;
                        window.scrollBy(0, distance);
                        totalHeight += distance;
                        
                        if (totalHeight >= scrollHeight) {
                            clearInterval(timer);
                            resolve();
                        }
                    }, 100);
                });
            });
            
            await page.waitForTimeout(2000);

            // Estrai le recensioni dalla pagina corrente
            const reviews = await page.evaluate(() => {
                const reviewSelectors = [
                    'article.styles_reviewCard__meSdm',
                    '[data-service-review-card-uid]',
                    '.styles_reviewCard__hcAvl',
                    '.review-card',
                    '[data-testid=\"review-card\"]',
                    '.typography_typography__QhxXQ',
                    '.styles_reviewCard__hcAvl',
                    '.review-item',
                    '.review',
                    'article[data-service-review-card-uid]',
                    'div[data-service-review-card-uid]',
                    '[data-testid=\"review\"]',
                    '.review-card__container',
                    'article[data-testid=\"review\"]',
                    'div[data-testid=\"review\"]',
                    '.review-card__container',
                    '[data-testid=\"review-card\"]'
                ];
                
                let reviewElements = [];
                for (const selector of reviewSelectors) {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length > 0) {
                        console.log(\`Trovati \${elements.length} elementi con selettore: \${selector}\`);
                        reviewElements = Array.from(elements);
                        break;
                    }
                }
                
                const reviews = [];
                reviewElements.forEach((element, index) => {
                    try {
                        // Nome utente
                        const nameSelectors = [
                            '.styles_consumerName__xKr9c',
                            '.typography_heading-xs__osRhC',
                            'button[aria-label*=\"Info for\"]',
                            '.styles_consumerInfoWrapper__6HN5O button',
                            '.styles_btnWrapper__arGPQ',
                            '[data-service-review-name-typography]',
                            '.typography_heading-xs__jSwUz',
                            '.reviewer-name',
                            '.reviewer__name',
                            '.consumer-name',
                            'h3',
                            'h4',
                            '[data-testid=\"reviewer-name\"]',
                            '.reviewer__display-name'
                        ];
                        let name = 'Utente anonimo';
                        for (const nameSelector of nameSelectors) {
                            const nameElement = element.querySelector(nameSelector);
                            if (nameElement && nameElement.textContent.trim()) {
                                name = nameElement.textContent.trim();
                                break;
                            }
                        }
                        
                        // Data
                        const dateSelectors = [
                            'time',
                            '.styles_consumerExtraDetails__NY6RP time',
                            '.typography_body-m__k2UI7 time',
                            '.review-date',
                            '.reviewer__date',
                            '[datetime]',
                            '.date',
                            '[data-testid=\"review-date\"]'
                        ];
                        let date = '';
                        for (const dateSelector of dateSelectors) {
                            const dateElement = element.querySelector(dateSelector);
                            if (dateElement) {
                                date = dateElement.getAttribute('datetime') || dateElement.textContent.trim();
                                if (date) break;
                            }
                        }
                        
                        // Rating
                        const ratingSelectors = [
                            '[data-service-review-rating]',
                            '.star-rating_starRating__4rrcf',
                            '.stars',
                            '.rating',
                            '[data-rating]',
                            '.review-rating',
                            '[data-testid=\"rating\"]',
                            '.star-rating'
                        ];
                        let rating = 0;
                        for (const ratingSelector of ratingSelectors) {
                            const ratingElement = element.querySelector(ratingSelector);
                            if (ratingElement) {
                                const ratingAttr = ratingElement.getAttribute('data-service-review-rating');
                                const altAttr = ratingElement.getAttribute('alt');
                                const text = ratingElement.textContent;
                                
                                if (ratingAttr) {
                                    rating = parseInt(ratingAttr);
                                    break;
                                } else if (altAttr) {
                                    const match = altAttr.match(/(\\d+)/);
                                    if (match) {
                                        rating = parseInt(match[1]);
                                        break;
                                    }
                                } else if (text) {
                                    const match = text.match(/(\\d+)/);
                                    if (match) {
                                        rating = parseInt(match[1]);
                                        break;
                                    }
                                }
                            }
                        }
                        
                        // Titolo
                        const titleSelectors = [
                            '[data-service-review-title-typography]',
                            '.typography_heading-s__f7029',
                            '.review-title',
                            '.reviewer__title',
                            'h3',
                            'h4',
                            '[data-testid=\"review-title\"]'
                        ];
                        let title = '';
                        for (const titleSelector of titleSelectors) {
                            const titleElement = element.querySelector(titleSelector);
                            if (titleElement && titleElement.textContent.trim()) {
                                title = titleElement.textContent.trim();
                                break;
                            }
                        }
                        
                        // Testo recensione
                        const textSelectors = [
                            '.styles_reviewText__q8Zhv',
                            '[data-service-review-text-typography]',
                            '.typography_body-l__KUYFJ',
                            '.review-content',
                            '.reviewer__content',
                            '.review-text',
                            'p',
                            '[data-testid=\"review-content\"]',
                            '.review-card__content'
                        ];
                        let text = '';
                        for (const textSelector of textSelectors) {
                            const textElement = element.querySelector(textSelector);
                            if (textElement && textElement.textContent.trim()) {
                                text = textElement.textContent.trim();
                                break;
                            }
                        }
                        
                        // Paese
                        const countrySelectors = [
                            '[data-service-review-country]',
                            '.reviewer-country',
                            '.consumer-country',
                            '.country'
                        ];
                        let country = '';
                        for (const countrySelector of countrySelectors) {
                            const countryElement = element.querySelector(countrySelector);
                            if (countryElement && countryElement.textContent.trim()) {
                                country = countryElement.textContent.trim();
                                break;
                            }
                        }
                        
                        // Solo se abbiamo almeno un nome o un titolo
                        if (name !== 'Utente anonimo' || title || text) {
                            reviews.push({
                                name,
                                date,
                                rating,
                                title,
                                text,
                                country
                            });
                        }
                    } catch (error) {
                        console.error(\`Errore parsing recensione \${index}:\`, error);
                    }
                });
                
                return reviews;
            });

            console.log(\`[Pagina \${pageNum}] Recensioni trovate: \${reviews.length}\`);
            allReviews = allReviews.concat(reviews);
            console.log(\`[Pagina \${pageNum}] Totale recensioni raccolte: \${allReviews.length}\`);

            // Cerca il pulsante \"Pagina successiva\" con selettori migliorati
            const nextSelectors = [
                'a[aria-label=\"Pagina successiva\"]',
                'a[aria-label=\"Next page\"]',
                'a[aria-label=\"Next\"]',
                'a[rel=\"next\"]',
                '.pagination-next a',
                'a[href*=\"page=\"]',
                'button[aria-label=\"Next\"]',
                'button[aria-label=\"Next page\"]',
                'button[aria-label=\"Pagina successiva\"]',
                '[data-testid=\"pagination-next\"]',
                '.pagination__next',
                'a[data-testid=\"pagination-next\"]',
                'button[data-testid=\"pagination-next\"]'
            ];
            
            let nextBtn = null;
            let nextUrl = null;
            
            // Prima prova a trovare il pulsante
            for (const selector of nextSelectors) {
                nextBtn = await page.$(selector);
                if (nextBtn) {
                    console.log(\`[Pagina \${pageNum}] Trovato pulsante next con selettore: \${selector}\`);
                    break;
                }
            }
            
            // Se non trova il pulsante, prova a cercare l'URL della prossima pagina
            if (!nextBtn) {
                console.log(\`[Pagina \${pageNum}] Cercando URL della prossima pagina...\`);
                nextUrl = await page.evaluate(() => {
                    const nextLinks = document.querySelectorAll('a[href*=\"page=\"]');
                    for (const link of nextLinks) {
                        const href = link.getAttribute('href');
                        if (href && href.includes('page=')) {
                            const pageMatch = href.match(/page=(\\d+)/);
                            if (pageMatch) {
                                const currentPage = parseInt(pageMatch[1]);
                                const nextPage = currentPage + 1;
                                return href.replace(/page=\\d+/, \`page=\${nextPage}\`);
                            }
                        }
                    }
                    return null;
                });
                
                if (nextUrl) {
                    console.log(\`[Pagina \${pageNum}] Trovato URL prossima pagina: \${nextUrl}\`);
                }
            }
            
            if (nextBtn) {
                const isVisible = await nextBtn.isVisible();
                const isEnabled = await nextBtn.isEnabled();
                console.log(\`[Pagina \${pageNum}] Pulsante next visibile: \${isVisible}, abilitato: \${isEnabled}\`);
                if (isVisible && isEnabled) {
                    await nextBtn.click();
                    await page.waitForTimeout(4000); // Aumentato il tempo di attesa
                    pageNum++;
                } else {
                    console.log(\`[Pagina \${pageNum}] Pulsante next non cliccabile, fine scraping.\`);
                    hasNext = false;
                }
            } else if (nextUrl) {
                console.log(\`[Pagina \${pageNum}] Navigazione diretta a: \${nextUrl}\`);
                await page.goto(nextUrl, { waitUntil: 'networkidle' });
                await page.waitForTimeout(3000);
                pageNum++;
            } else {
                console.log(\`[Pagina \${pageNum}] Nessun pulsante next o URL trovato, fine scraping.\`);
                hasNext = false;
            }
        }

        await browser.close();
        console.log(\`Scraping completato. Totale recensioni raccolte: \${allReviews.length}\`);
        
        // Output JSON per PHP
        console.log('JSON_START');
        console.log(JSON.stringify({
            success: true,
            reviews: allReviews
        }));
        console.log('JSON_END');
        
    } catch (error) {
        console.error('Errore durante lo scraping:', error);
        if (browser) await browser.close();
        console.log('JSON_START');
        console.log(JSON.stringify({
            success: false,
            error: error.message,
            reviews: []
        }));
        console.log('JSON_END');
    }
})();
";
    }
    
    /**
     * Crea un file temporaneo con lo script
     */
    private function create_temp_script($content) {
        $temp_file = tempnam(sys_get_temp_dir(), 'playwright_');
        file_put_contents($temp_file, $content);
        return $temp_file;
    }
    
    /**
     * Esegue Playwright
     */
    private function execute_playwright($script_file) {
        $command = "cd " . plugin_dir_path(__FILE__) . ".. && {$this->node_path} $script_file 2>&1";
        
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        // Cerca il JSON nell'output
        $json_start = false;
        $json_content = '';
        
        foreach ($output as $line) {
            if (trim($line) === 'JSON_START') {
                $json_start = true;
                continue;
            }
            if (trim($line) === 'JSON_END') {
                break;
            }
            if ($json_start) {
                $json_content .= $line . "\n";
            }
        }
        
        if ($json_content) {
            $data = json_decode($json_content, true);
            if ($data && isset($data['success'])) {
                return $data;
            }
        }
        
        error_log('Playwright Scraper - Output: ' . implode("\n", $output));
        return false;
    }
} 