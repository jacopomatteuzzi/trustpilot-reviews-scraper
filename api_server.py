#!/usr/bin/env python3
"""
Trustpilot Reviews Scraper API
Servizio web Flask per scraping Trustpilot
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import requests
from bs4 import BeautifulSoup
import json
import time
import random
import re
from datetime import datetime
import os

app = Flask(__name__)
CORS(app)  # Permette richieste cross-origin

class TrustpilotScraper:
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'it-IT,it;q=0.9,en;q=0.8',
            'Accept-Encoding': 'gzip, deflate, br',
            'DNT': '1',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
        })
        
    def scrape_reviews(self, company_url, max_pages=10):
        """
        Scrapa le recensioni da Trustpilot
        """
        print(f"🚀 Inizio scraping: {company_url}")
        
        all_reviews = []
        current_page = 1
        
        # Assicurati che l'URL sia nella sezione recensioni
        if '/review/' not in company_url:
            company_url = company_url.replace('trustpilot.com', 'trustpilot.com/review')
        
        while current_page <= max_pages:
            print(f"📄 Pagina {current_page}")
            
            # Costruisci URL della pagina
            if current_page == 1:
                page_url = company_url
            else:
                page_url = f"{company_url}?page={current_page}"
            
            try:
                # Richiesta HTTP
                response = self.session.get(page_url, timeout=30)
                response.raise_for_status()
                
                # Parse HTML
                soup = BeautifulSoup(response.content, 'html.parser')
                
                # Estrai recensioni dalla pagina
                page_reviews = self.extract_reviews_from_page(soup)
                
                if not page_reviews:
                    print(f"❌ Nessuna recensione trovata nella pagina {current_page}")
                    break
                
                print(f"✅ Trovate {len(page_reviews)} recensioni nella pagina {current_page}")
                all_reviews.extend(page_reviews)
                
                # Verifica se c'è una pagina successiva
                if not self.has_next_page(soup):
                    print("🏁 Ultima pagina raggiunta")
                    break
                
                current_page += 1
                
                # Pausa per evitare di essere bloccato
                time.sleep(random.uniform(1, 3))
                
            except requests.RequestException as e:
                print(f"❌ Errore di rete: {e}")
                break
            except Exception as e:
                print(f"❌ Errore generico: {e}")
                break
        
        print(f"🎉 Scraping completato. Totale recensioni: {len(all_reviews)}")
        return all_reviews
    
    def extract_reviews_from_page(self, soup):
        """
        Estrae le recensioni da una pagina HTML
        """
        reviews = []
        
        # Selettori per le recensioni (aggiornati)
        review_selectors = [
            'article.styles_reviewCard__meSdm',
            'article[data-service-review-card-uid]',
            '.styles_reviewCard__hcAvl',
            '.review-card',
            '[data-testid="review-card"]'
        ]
        
        review_elements = []
        for selector in review_selectors:
            review_elements = soup.select(selector)
            if review_elements:
                print(f"✅ Trovati {len(review_elements)} elementi con selettore: {selector}")
                break
        
        for element in review_elements:
            try:
                review = self.extract_single_review(element)
                if review:
                    reviews.append(review)
            except Exception as e:
                print(f"⚠️ Errore estrazione recensione: {e}")
                continue
        
        return reviews
    
    def extract_single_review(self, element):
        """
        Estrae i dati di una singola recensione
        """
        review = {
            'name': 'Utente anonimo',
            'rating': 0,
            'title': '',
            'text': '',
            'date': '',
            'country': ''
        }
        
        # Nome utente
        name_selectors = [
            '.styles_consumerName__xKr9c',
            '.typography_heading-xs__osRhC',
            'button[aria-label*="Info for"]',
            '.styles_consumerInfoWrapper__6HN5O button',
            '.styles_btnWrapper__arGPQ',
            '[data-service-review-name-typography]',
            '.reviewer-name',
            '.consumer-name'
        ]
        
        for selector in name_selectors:
            name_elem = element.select_one(selector)
            if name_elem:
                name_text = name_elem.get_text(strip=True)
                if name_text and name_text != 'Utente anonimo':
                    review['name'] = name_text
                    break
        
        # Rating
        rating_selectors = [
            '[data-service-review-rating]',
            '.star-rating',
            '.stars',
            '[data-rating]',
            'img[alt*="star"]',
            'img[alt*="stelle"]'
        ]
        
        for selector in rating_selectors:
            rating_elem = element.select_one(selector)
            if rating_elem:
                # Prova diversi metodi per estrarre il rating
                rating_attr = rating_elem.get('data-service-review-rating')
                if rating_attr:
                    try:
                        review['rating'] = int(rating_attr)
                        break
                    except ValueError:
                        pass
                
                # Prova dall'alt text
                alt_text = rating_elem.get('alt', '')
                if 'star' in alt_text.lower():
                    # Estrai numero da "5 stars" o "5 stelle"
                    match = re.search(r'(\d+)', alt_text)
                    if match:
                        review['rating'] = int(match.group(1))
                        break
        
        # Titolo
        title_selectors = [
            'h2',
            'h3',
            'h4',
            '.review-title',
            '[data-testid="review-title"]'
        ]
        
        for selector in title_selectors:
            title_elem = element.select_one(selector)
            if title_elem:
                title_text = title_elem.get_text(strip=True)
                if title_text:
                    review['title'] = title_text
                    break
        
        # Testo recensione
        text_selectors = [
            '.styles_reviewText__q8Zhv',
            '[data-service-review-text-typography]',
            '.typography_body-l__KUYFJ',
            '.review-content',
            '.review-text',
            'p',
            '[data-testid="review-content"]'
        ]
        
        for selector in text_selectors:
            text_elem = element.select_one(selector)
            if text_elem:
                text_content = text_elem.get_text(strip=True)
                if text_content and len(text_content) > 10:  # Evita testi troppo corti
                    review['text'] = text_content
                    break
        
        # Data
        date_selectors = [
            'time',
            '.styles_consumerExtraDetails__NY6RP time',
            '.typography_body-m__k2UI7 time',
            '.review-date',
            '[datetime]',
            '.date'
        ]
        
        for selector in date_selectors:
            date_elem = element.select_one(selector)
            if date_elem:
                # Prova prima l'attributo datetime
                datetime_attr = date_elem.get('datetime')
                if datetime_attr:
                    review['date'] = datetime_attr
                    break
                
                # Altrimenti usa il testo
                date_text = date_elem.get_text(strip=True)
                if date_text:
                    review['date'] = date_text
                    break
        
        # Paese (opzionale)
        country_selectors = [
            '[data-service-review-country]',
            '.reviewer-country',
            '.consumer-country'
        ]
        
        for selector in country_selectors:
            country_elem = element.select_one(selector)
            if country_elem:
                country_text = country_elem.get_text(strip=True)
                if country_text:
                    review['country'] = country_text
                    break
        
        # Ritorna solo se abbiamo almeno un nome o un testo
        if review['name'] != 'Utente anonimo' or review['text']:
            return review
        
        return None
    
    def has_next_page(self, soup):
        """
        Verifica se c'è una pagina successiva
        """
        next_selectors = [
            'a[aria-label="Next page"]',
            'a[aria-label="Next"]',
            'a[aria-label="Pagina successiva"]',
            'a[rel="next"]',
            '.pagination-next a',
            'a[href*="page="]'
        ]
        
        for selector in next_selectors:
            next_elem = soup.select_one(selector)
            if next_elem and next_elem.get('href'):
                return True
        
        return False

# Istanza globale dello scraper
scraper = TrustpilotScraper()

@app.route('/')
def home():
    """Homepage con informazioni API"""
    return jsonify({
        'service': 'Trustpilot Reviews Scraper API',
        'version': '1.0.0',
        'endpoints': {
            '/scrape': 'POST - Scrapa recensioni Trustpilot',
            '/health': 'GET - Health check'
        },
        'usage': {
            'method': 'POST',
            'url': '/scrape',
            'body': {
                'url': 'https://www.trustpilot.com/review/example.com',
                'max_pages': 10
            }
        }
    })

@app.route('/health')
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'timestamp': datetime.now().isoformat()
    })

@app.route('/scrape', methods=['POST'])
def scrape():
    """Endpoint principale per lo scraping"""
    try:
        # Ottieni i dati dalla richiesta
        data = request.get_json()
        
        if not data or 'url' not in data:
            return jsonify({
                'success': False,
                'error': 'URL richiesto nel body della richiesta'
            }), 400
        
        url = data['url']
        max_pages = data.get('max_pages', 10)
        
        # Validazione URL
        if 'trustpilot.com' not in url:
            return jsonify({
                'success': False,
                'error': 'URL deve essere di Trustpilot'
            }), 400
        
        # Esegui lo scraping
        reviews = scraper.scrape_reviews(url, max_pages)
        
        # Ritorna i risultati
        return jsonify({
            'success': True,
            'reviews': reviews,
            'count': len(reviews),
            'url': url,
            'max_pages': max_pages
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/scrape', methods=['GET'])
def scrape_get():
    """Endpoint GET per test rapidi"""
    url = request.args.get('url')
    
    if not url:
        return jsonify({
            'success': False,
            'error': 'Parametro URL richiesto'
        }), 400
    
    try:
        max_pages = int(request.args.get('max_pages', 5))
        reviews = scraper.scrape_reviews(url, max_pages)
        
        return jsonify({
            'success': True,
            'reviews': reviews,
            'count': len(reviews),
            'url': url
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    # Configurazione per Render.com
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port, debug=False) 