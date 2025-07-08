<?php
/**
 * Python Scraper Class
 * Gestisce lo scraping delle recensioni Trustpilot usando Python + BeautifulSoup
 */

if (!defined('ABSPATH')) {
    exit;
}

class PythonScraper {
    
    private $python_path;
    private $script_path;
    
    public function __construct() {
        // Gestisce sia l'ambiente WordPress che i test
        if (function_exists('plugin_dir_path')) {
            $this->script_path = plugin_dir_path(__FILE__) . '../scraper.py';
        } else {
            $this->script_path = dirname(__FILE__) . '/../scraper.py';
        }
        $this->python_path = $this->find_python_path();
    }
    
    /**
     * Trova il percorso di Python
     */
    private function find_python_path() {
        $possible_paths = [
            'python3',
            'python',
            '/usr/bin/python3',
            '/usr/local/bin/python3',
            '/opt/homebrew/bin/python3'
        ];
        
        foreach ($possible_paths as $path) {
            if ($this->is_command_available($path)) {
                return $path;
            }
        }
        
        return 'python3';
    }
    
    /**
     * Verifica se un comando è disponibile
     */
    private function is_command_available($command) {
        // Su hosting condivisi, evita di usare exec()
        if (!function_exists('exec')) {
            return false;
        }
        
        $output = [];
        $return_var = 0;
        @exec("which $command 2>/dev/null", $output, $return_var);
        return $return_var === 0;
    }
    
    /**
     * Scrapa le recensioni usando Python
     */
    public function scrape_reviews($company_url) {
        try {
            // Verifica che Python sia disponibile
            if (!$this->is_command_available($this->python_path)) {
                throw new Exception('Python non è disponibile sul server. Verifica che Python 3 sia installato.');
            }
            
            // Verifica che lo script esista
            if (!file_exists($this->script_path)) {
                throw new Exception('Script Python non trovato: ' . $this->script_path);
            }
            
            // Verifica che le dipendenze siano installate
            $this->check_python_dependencies();
            
            // Esegui Python
            $result = $this->execute_python($company_url);
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Python Scraper - Errore: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'reviews' => []
            ];
        }
    }
    
    /**
     * Verifica che le dipendenze Python siano installate
     */
    private function check_python_dependencies() {
        $check_script = "import requests, bs4, json, sys; print('OK')";
        $output = [];
        $return_var = 0;
        
        @exec("$this->python_path -c \"$check_script\" 2>&1", $output, $return_var);
        
        if ($return_var !== 0) {
            throw new Exception('Dipendenze Python mancanti. Installa con: pip install requests beautifulsoup4 lxml');
        }
    }
    
    /**
     * Esegue lo script Python
     */
    private function execute_python($company_url) {
        // Sanitizza l'URL
        $company_url = escapeshellarg($company_url);
        
        // Comando per eseguire Python
        $command = "$this->python_path " . escapeshellarg($this->script_path) . " $company_url 2>&1";
        
        // Esegui il comando
        $output = [];
        $return_var = 0;
        
        @exec($command, $output, $return_var);
        
        // Combina l'output
        $full_output = implode("\n", $output);
        
        // Log per debug
        error_log('Python Scraper - Comando eseguito: ' . $command);
        error_log('Python Scraper - Output: ' . $full_output);
        
        if ($return_var !== 0) {
            throw new Exception('Errore esecuzione Python: ' . $full_output);
        }
        
        // Cerca l'output JSON
        $json_start = strpos($full_output, '{');
        if ($json_start === false) {
            throw new Exception('Output JSON non trovato: ' . $full_output);
        }
        
        $json_output = substr($full_output, $json_start);
        $result = json_decode($json_output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Errore parsing JSON: ' . json_last_error_msg() . ' - Output: ' . $json_output);
        }
        
        return $result;
    }
    
    /**
     * Test di funzionamento
     */
    public function test() {
        try {
            $test_url = 'https://www.trustpilot.com/review/amazon.com';
            $result = $this->scrape_reviews($test_url);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Test completato con successo. Recensioni trovate: ' . count($result['reviews'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Test fallito: ' . $result['error']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Test fallito: ' . $e->getMessage()
            ];
        }
    }
} 