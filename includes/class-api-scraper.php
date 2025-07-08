<?php
/**
 * API Scraper Class
 * Gestisce lo scraping delle recensioni Trustpilot tramite API esterna
 */

if (!defined('ABSPATH')) {
    exit;
}

class ApiScraper {
    
    private $api_url;
    
    public function __construct() {
        // URL dell'API su Render.com (da sostituire con il tuo URL)
        $this->api_url = 'https://your-app-name.onrender.com';
        
        // Permetti di personalizzare l'URL dell'API
        $this->api_url = apply_filters('trs_api_url', $this->api_url);
    }
    
    /**
     * Scrapa le recensioni usando l'API esterna
     */
    public function scrape_reviews($company_url) {
        try {
            // Verifica che l'URL sia valido
            if (empty($company_url) || !filter_var($company_url, FILTER_VALIDATE_URL)) {
                throw new Exception('URL non valido');
            }
            
            // Verifica che sia un URL Trustpilot
            if (strpos($company_url, 'trustpilot.com') === false) {
                throw new Exception('URL deve essere di Trustpilot');
            }
            
            // Prepara i dati per la richiesta
            $request_data = array(
                'url' => $company_url,
                'max_pages' => 10
            );
            
            // Effettua la richiesta all'API
            $response = $this->make_api_request($request_data);
            
            if (!$response || !isset($response['success']) || !$response['success']) {
                $error_message = isset($response['error']) ? $response['error'] : 'Errore sconosciuto durante lo scraping';
                throw new Exception($error_message);
            }
            
            $reviews = $response['reviews'];
            
            if (empty($reviews)) {
                throw new Exception('Nessuna recensione trovata. Verifica che l\'URL sia corretto e che l\'azienda abbia recensioni su Trustpilot.');
            }
            
            // Log del numero di recensioni trovate
            error_log('API Scraper - Recensioni trovate: ' . count($reviews));
            
            return $reviews;
            
        } catch (Exception $e) {
            error_log('API Scraper - Errore: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'reviews' => []
            ];
        }
    }
    
    /**
     * Effettua la richiesta all'API
     */
    private function make_api_request($data) {
        $api_endpoint = $this->api_url . '/scrape';
        
        // Prepara la richiesta HTTP
        $args = array(
            'method' => 'POST',
            'timeout' => 120, // 2 minuti di timeout
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'body' => json_encode($data),
            'sslverify' => true
        );
        
        // Effettua la richiesta
        $response = wp_remote_post($api_endpoint, $args);
        
        // Verifica se la richiesta è andata a buon fine
        if (is_wp_error($response)) {
            throw new Exception('Errore di connessione: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Log per debug
        error_log('API Scraper - Response Code: ' . $response_code);
        error_log('API Scraper - Response Body: ' . $response_body);
        
        // Verifica il codice di risposta
        if ($response_code !== 200) {
            throw new Exception('Errore API (HTTP ' . $response_code . '): ' . $response_body);
        }
        
        // Decodifica la risposta JSON
        $result = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Errore parsing JSON: ' . json_last_error_msg());
        }
        
        return $result;
    }
    
    /**
     * Test di connessione all'API
     */
    public function test_connection() {
        try {
            $health_endpoint = $this->api_url . '/health';
            
            $response = wp_remote_get($health_endpoint, array(
                'timeout' => 30,
                'sslverify' => true
            ));
            
            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'message' => 'Errore di connessione: ' . $response->get_error_message()
                ];
            }
            
            $response_code = wp_remote_retrieve_response_code($response);
            
            if ($response_code === 200) {
                return [
                    'success' => true,
                    'message' => 'Connessione API funzionante'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Errore API (HTTP ' . $response_code . ')'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Errore test: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Test di scraping
     */
    public function test_scraping() {
        try {
            $test_url = 'https://www.trustpilot.com/review/amazon.com';
            $result = $this->scrape_reviews($test_url);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Test scraping completato. Recensioni trovate: ' . count($result['reviews'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Test scraping fallito: ' . $result['error']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Test scraping fallito: ' . $e->getMessage()
            ];
        }
    }
} 