<?php
define('SUPABASE_URL', 'https://ijrnahatonxpuzwjtykd.supabase.co'); // no trailing slash
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imlqcm5haGF0b254cHV6d2p0eWtkIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTU2NzMwMDYsImV4cCI6MjA3MTI0OTAwNn0.cTqgwDjRywsc-Gq8_bolSGT-rzQRr4GONrs6W8VXc8E');
define('SUPABASE_SERVICE_ROLE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imlqcm5haGF0b254cHV6d2p0eWtkIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1NTY3MzAwNiwiZXhwIjoyMDcxMjQ5MDA2fQ.Il9Ydbdt_phqJyN09FDg9Dqvb_vZOtLEAi7EIz80B3Y'); // Add your service role key here

class SupabaseDB {
    private $baseUrl;
    private $anonKey;
    private $userToken;
    private $useServiceRole;
    
    public function __construct($userToken = null, $useServiceRole = false) {
        $this->baseUrl = SUPABASE_URL;
        $this->anonKey = SUPABASE_ANON_KEY;
        $this->userToken = $userToken;
        $this->useServiceRole = $useServiceRole;
    }

    // Page title configuration
function getPageTitle() {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    
    $page_titles = [
        'home' => 'Dashboard',
        'dashboard' => 'Dashboard',
        'index' => 'Dashboard',
        'drivers' => 'Drivers Management',
        'passengers' => 'Passengers Management',
        'rides' => 'Rides Management',
        'bookings' => 'Bookings',
        'reports' => 'Reports & Analytics',
        'settings' => 'Settings',
        'profile' => 'My Profile',
        'vehicles' => 'Vehicle Management',
        'payments' => 'Payment Transactions',
    ];
    
    return isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Dashboard';
}
    
    /**
     * Fetch data from a Supabase table
     * @param string $tableName - Name of the table to query
     * @param array $params - Optional query parameters (select, filter, order, limit, offset, page)
     * @return array - Array of records
     */
    public function fetchData($tableName, $params = []) {
        $url = $this->baseUrl . '/rest/v1/' . $tableName;
        
        // Build query string
        $queryParams = [];
        if (isset($params['select'])) {
            $queryParams[] = 'select=' . urlencode($params['select']);
        }
        if (isset($params['order'])) {
            $queryParams[] = 'order=' . urlencode($params['order']);
        }
        
        // Handle pagination: use limit/offset or page/limit
        $limit = isset($params['limit']) ? intval($params['limit']) : null;
        $offset = null;
        
        if (isset($params['page']) && isset($params['limit'])) {
            $page = max(1, intval($params['page']));
            $limit = intval($params['limit']);
            $offset = ($page - 1) * $limit;
        } elseif (isset($params['offset'])) {
            $offset = intval($params['offset']);
        }
        
        if ($limit !== null) {
            $queryParams[] = 'limit=' . $limit;
        }
        if ($offset !== null) {
            $queryParams[] = 'offset=' . $offset;
        }
        
        if (!empty($queryParams)) {
            $url .= '?' . implode('&', $queryParams);
        }
        
        // Initialize cURL
        $ch = curl_init();
        
        if ($this->useServiceRole && defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '') {
            $authToken = SUPABASE_SERVICE_ROLE_KEY;
            $apiKey = SUPABASE_SERVICE_ROLE_KEY;
        } else {
            $authToken = $this->userToken ? $this->userToken : $this->anonKey;
            $apiKey = $this->anonKey;
        }
        
        $headers = [
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $authToken,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        // Add Range header for pagination (Supabase uses Range header)
        if ($limit !== null && $offset !== null) {
            $rangeStart = $offset;
            $rangeEnd = $offset + $limit - 1;
            $headers[] = 'Range: ' . $rangeStart . '-' . $rangeEnd;
        } elseif ($limit !== null) {
            $headers[] = 'Range: 0-' . ($limit - 1);
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => true, // Include headers in response to get Content-Range
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        if ($error) {
            error_log("Supabase cURL Error: " . $error);
            throw new Exception("Failed to connect to Supabase: " . $error);
        }
        
        // Parse headers and body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        if ($httpCode !== 200 && $httpCode !== 206) {
            error_log("Supabase API Error: HTTP $httpCode - URL: $url - Response: " . substr($body, 0, 500));
            throw new Exception("Supabase API returned error code: $httpCode. Response: " . substr($body, 0, 200));
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Supabase JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($body, 0, 500));
            throw new Exception("Failed to parse Supabase response: " . json_last_error_msg());
        }
        
        error_log("Supabase fetchData - Table: $tableName - Records returned: " . (is_array($data) ? count($data) : 0));
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Get total count of records in a Supabase table
     * @param string $tableName - Name of the table to query
     * @param array $params - Optional query parameters (filter conditions)
     * @return int - Total count of records
     */
    public function getCount($tableName, $params = []) {
        $url = $this->baseUrl . '/rest/v1/' . $tableName;
        
        // Build query string for filters
        $queryParams = ['select' => 'id'];
        if (isset($params['filter'])) {
            // Add filter conditions if provided
            foreach ($params['filter'] as $column => $value) {
                if ($value !== null && $value !== '') {
                    $queryParams[] = $column . '=eq.' . urlencode($value);
                }
            }
        }
        
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        // Initialize cURL
        $ch = curl_init();
        
        if ($this->useServiceRole && defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '') {
            $authToken = SUPABASE_SERVICE_ROLE_KEY;
            $apiKey = SUPABASE_SERVICE_ROLE_KEY;
        } else {
            $authToken = $this->userToken ? $this->userToken : $this->anonKey;
            $apiKey = $this->anonKey;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $apiKey,
                'Authorization: Bearer ' . $authToken,
                'Content-Type: application/json',
                'Prefer: count=exact'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        if ($error) {
            error_log("Supabase cURL Error: " . $error);
            throw new Exception("Failed to connect to Supabase: " . $error);
        }
        
        // Parse headers to get Content-Range
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        
        if ($httpCode !== 200 && $httpCode !== 206) {
            error_log("Supabase API Error: HTTP $httpCode - URL: $url");
            throw new Exception("Supabase API returned error code: $httpCode");
        }
        
        // Extract count from Content-Range header
        if (preg_match('/Content-Range:\s*\d+-\d+\/(\d+)/i', $headers, $matches)) {
            return intval($matches[1]);
        }
        
        // Fallback: count the returned records
        $body = substr($response, $headerSize);
        $data = json_decode($body, true);
        return is_array($data) ? count($data) : 0;
    }

       /**
     * Insert data into a Supabase table
     * @param string $tableName - Name of the table to insert into
     * @param array $data - Data to insert (associative array)
     * @return array - Inserted record(s)
     */
    public function insertData($tableName, $data) {
        $url = $this->baseUrl . '/rest/v1/' . $tableName;

        // Initialize cURL
        $ch = curl_init();

        if ($this->useServiceRole && defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '') {
            $authToken = SUPABASE_SERVICE_ROLE_KEY;
            $apiKey = SUPABASE_SERVICE_ROLE_KEY;
        } else {
            $authToken = $this->userToken ? $this->userToken : $this->anonKey;
            $apiKey = $this->anonKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $apiKey,
                'Authorization: Bearer ' . $authToken,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            error_log("Supabase cURL Error: " . $error);
            throw new Exception("Failed to connect to Supabase: " . $error);
        }

        if ($httpCode !== 201 && $httpCode !== 200) {
            error_log("Supabase API Error: HTTP $httpCode - URL: $url - Response: " . substr($response, 0, 500));
            throw new Exception("Supabase API returned error code: $httpCode. Response: " . substr($response, 0, 200));
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Supabase JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 500));
            throw new Exception("Failed to parse Supabase response: " . json_last_error_msg());
        }

        error_log("Supabase insertData - Table: $tableName - Record inserted successfully");

        return is_array($data) ? (isset($data[0]) ? $data[0] : $data) : [];
    }

    /**
     * Find data in a Supabase table with filters
     * @param string $tableName - Name of the table to query
     * @param array $filters - Filter conditions (e.g., ['phone' => '+353123456789'])
     * @return array - Array of matching records
     */
    public function findData($tableName, $filters = []) {
        $url = $this->baseUrl . '/rest/v1/' . $tableName;

        // Build query string with filters
        $queryParams = [];
        foreach ($filters as $column => $value) {
            // Skip null/empty filters to avoid invalid query parts and deprecation warnings
            if ($value === null || $value === '') {
                continue;
            }
            $queryParams[] = $column . '=eq.' . urlencode($value);
        }

        if (!empty($queryParams)) {
            $url .= '?' . implode('&', $queryParams);
        }

        // Initialize cURL
        $ch = curl_init();

        if ($this->useServiceRole && defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '') {
            $authToken = SUPABASE_SERVICE_ROLE_KEY;
            $apiKey = SUPABASE_SERVICE_ROLE_KEY;
        } else {
            $authToken = $this->userToken ? $this->userToken : $this->anonKey;
            $apiKey = $this->anonKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $apiKey,
                'Authorization: Bearer ' . $authToken,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            error_log("Supabase cURL Error: " . $error);
            throw new Exception("Failed to connect to Supabase: " . $error);
        }

        if ($httpCode !== 200) {
            error_log("Supabase API Error: HTTP $httpCode - URL: $url - Response: " . substr($response, 0, 500));
            throw new Exception("Supabase API returned error code: $httpCode. Response: " . substr($response, 0, 200));
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Supabase JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 500));
            throw new Exception("Failed to parse Supabase response: " . json_last_error_msg());
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Update data in a Supabase table
     * @param string $tableName - Name of the table to update
     * @param string $id - ID of the record to update
     * @param array $data - Data to update (associative array)
     * @return array - Updated record
     */
    public function updateData($tableName, $id, $data) {
        $url = $this->baseUrl . '/rest/v1/' . $tableName . '?id=eq.' . urlencode($id);

        // Initialize cURL
        $ch = curl_init();

        if ($this->useServiceRole && defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '') {
            $authToken = SUPABASE_SERVICE_ROLE_KEY;
            $apiKey = SUPABASE_SERVICE_ROLE_KEY;
        } else {
            $authToken = $this->userToken ? $this->userToken : $this->anonKey;
            $apiKey = $this->anonKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $apiKey,
                'Authorization: Bearer ' . $authToken,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            error_log("Supabase cURL Error: " . $error);
            throw new Exception("Failed to connect to Supabase: " . $error);
        }

        if ($httpCode !== 200 && $httpCode !== 204) {
            error_log("Supabase API Error: HTTP $httpCode - URL: $url - Response: " . substr($response, 0, 500));
            throw new Exception("Supabase API returned error code: $httpCode. Response: " . substr($response, 0, 200));
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE && $httpCode !== 204) {
            error_log("Supabase JSON Decode Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 500));
            throw new Exception("Failed to parse Supabase response: " . json_last_error_msg());
        }

        error_log("Supabase updateData - Table: $tableName - Record updated successfully");

        return is_array($data) && isset($data[0]) ? $data[0] : ($data ?: []);
    }

    /**
     * Delete data from a Supabase table
     * @param string $tableName - Name of the table to delete from
     * @param string $id - ID of the record to delete
     * @return bool - True if successful
     */
    public function deleteData($tableName, $id) {
        $url = $this->baseUrl . '/rest/v1/' . $tableName . '?id=eq.' . urlencode($id);

        // Initialize cURL
        $ch = curl_init();

        if ($this->useServiceRole && defined('SUPABASE_SERVICE_ROLE_KEY') && SUPABASE_SERVICE_ROLE_KEY !== '') {
            $authToken = SUPABASE_SERVICE_ROLE_KEY;
            $apiKey = SUPABASE_SERVICE_ROLE_KEY;
        } else {
            $authToken = $this->userToken ? $this->userToken : $this->anonKey;
            $apiKey = $this->anonKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $apiKey,
                'Authorization: Bearer ' . $authToken,
                'Content-Type: application/json',
                'Prefer: return=minimal'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            error_log("Supabase cURL Error: " . $error);
            throw new Exception("Failed to connect to Supabase: " . $error);
        }

        if ($httpCode !== 200 && $httpCode !== 204) {
            error_log("Supabase API Error: HTTP $httpCode - URL: $url - Response: " . substr($response, 0, 500));
            throw new Exception("Supabase API returned error code: $httpCode. Response: " . substr($response, 0, 200));
        }

        error_log("Supabase deleteData - Table: $tableName - Record deleted successfully");

        return true;
    }
}
