<?php

namespace MachinesSMM;

use Exception;

/**
 * Machines SMM API Client
 * 
 * Refactored version with security improvements, error handling, and better code quality
 */
class Api
{
    /** @var string API URL */
    private string $api_url = 'https://machinesmm.com/api/v2';

    /** @var string API key from environment */
    private string $api_key;

    /** @var int cURL timeout in seconds */
    private int $timeout = 30;

    /** @var bool Enable debug logging */
    private bool $debug = false;

    /**
     * Constructor
     *
     * @param string|null $apiKey API key (if null, reads from MACHINES_SMM_API_KEY env var)
     * @param bool $debug Enable debug mode
     * @throws Exception
     */
    public function __construct(?string $apiKey = null, bool $debug = false)
    {
        $this->api_key = $apiKey ?? $this->getApiKeyFromEnv();
        $this->debug = $debug;

        if (empty($this->api_key)) {
            throw new Exception('API key not provided and MACHINES_SMM_API_KEY environment variable not set');
        }
    }

    /**
     * Get API key from environment variable
     *
     * @return string
     */
    private function getApiKeyFromEnv(): string
    {
        return getenv('MACHINES_SMM_API_KEY') ?: '';
    }

    /**
     * Set API URL (for testing purposes)
     *
     * @param string $url
     * @return self
     */
    public function setApiUrl(string $url): self
    {
        $this->api_url = $url;
        return $this;
    }

    /**
     * Set timeout for API calls
     *
     * @param int $seconds
     * @return self
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeout = max(1, $seconds);
        return $this;
    }

    /**
     * Add order
     *
     * @param array $data Order parameters
     * @return object
     * @throws Exception
     */
    public function order(array $data): object
    {
        $this->validateOrderData($data);
        $payload = array_merge(
            ['key' => $this->api_key, 'action' => 'add'],
            $data
        );
        return $this->connect($payload);
    }

    /**
     * Get order status
     *
     * @param int $orderId
     * @return object
     * @throws Exception
     */
    public function status(int $orderId): object
    {
        return $this->connect([
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $orderId,
        ]);
    }

    /**
     * Get multiple orders status
     *
     * @param array $orderIds
     * @return object
     * @throws Exception
     */
    public function multiStatus(array $orderIds): object
    {
        if (empty($orderIds)) {
            throw new Exception('At least one order ID is required');
        }

        return $this->connect([
            'key' => $this->api_key,
            'action' => 'status',
            'orders' => implode(',', array_map('intval', $orderIds)),
        ]);
    }

    /**
     * Get available services
     *
     * @return object
     * @throws Exception
     */
    public function services(): object
    {
        return $this->connect([
            'key' => $this->api_key,
            'action' => 'services',
        ]);
    }

    /**
     * Refill order
     *
     * @param int $orderId
     * @return object
     * @throws Exception
     */
    public function refill(int $orderId): object
    {
        return $this->connect([
            'key' => $this->api_key,
            'action' => 'refill',
            'order' => $orderId,
        ]);
    }

    /**
     * Refill multiple orders
     *
     * @param array $orderIds
     * @return object
     * @throws Exception
     */
    public function multiRefill(array $orderIds): object
    {
        if (empty($orderIds)) {
            throw new Exception('At least one order ID is required');
        }

        return $this->connect([
            'key' => $this->api_key,
            'action' => 'refill',
            'orders' => implode(',', array_map('intval', $orderIds)),
        ]);
    }

    /**
     * Get refill status
     *
     * @param int $refillId
     * @return object
     * @throws Exception
     */
    public function refillStatus(int $refillId): object
    {
        return $this->connect([
            'key' => $this->api_key,
            'action' => 'refill_status',
            'refill' => $refillId,
        ]);
    }

    /**
     * Get multiple refill statuses
     *
     * @param array $refillIds
     * @return object
     * @throws Exception
     */
    public function multiRefillStatus(array $refillIds): object
    {
        if (empty($refillIds)) {
            throw new Exception('At least one refill ID is required');
        }

        return $this->connect([
            'key' => $this->api_key,
            'action' => 'refill_status',
            'refills' => implode(',', array_map('intval', $refillIds)),
        ]);
    }

    /**
     * Cancel orders
     *
     * @param array $orderIds
     * @return object
     * @throws Exception
     */
    public function cancel(array $orderIds): object
    {
        if (empty($orderIds)) {
            throw new Exception('At least one order ID is required');
        }

        return $this->connect([
            'key' => $this->api_key,
            'action' => 'cancel',
            'orders' => implode(',', array_map('intval', $orderIds)),
        ]);
    }

    /**
     * Get account balance
     *
     * @return object
     * @throws Exception
     */
    public function balance(): object
    {
        return $this->connect([
            'key' => $this->api_key,
            'action' => 'balance',
        ]);
    }

    /**
     * Make API request
     *
     * @param array $post POST data
     * @return object
     * @throws Exception
     */
    private function connect(array $post): object
    {
        $this->log('Request payload', $post);

        $ch = curl_init($this->api_url);

        if (!$ch) {
            throw new Exception('Failed to initialize cURL');
        }

        // Use http_build_query for proper URL encoding
        $postData = http_build_query($post, '', '&', PHP_QUERY_RFC1738);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,        // ✅ ENABLED for security
            CURLOPT_SSL_VERIFYHOST => 2,           // ✅ STRICT verification
            CURLOPT_USERAGENT => 'MachinesSMM-PHP-Client/2.0',
        ]);

        $result = curl_exec($ch);
        $curlError = curl_errno($ch);
        $curlErrorMsg = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // Handle cURL errors
        if ($curlError !== 0) {
            throw new Exception("cURL Error ({$curlError}): {$curlErrorMsg}");
        }

        // Handle empty response
        if (empty($result)) {
            throw new Exception("API returned empty response (HTTP {$httpCode})");
        }

        // Decode JSON response
        $decoded = json_decode($result);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        $this->log('API Response', $decoded);

        return $decoded;
    }

    /**
     * Validate order data before sending
     *
     * @param array $data
     * @return void
     * @throws Exception
     */
    private function validateOrderData(array $data): void
    {
        $required = ['service', 'link'];
        $missing = array_diff($required, array_keys($data));

        if (!empty($missing)) {
            throw new Exception('Missing required order parameters: ' . implode(', ', $missing));
        }

        if (!is_numeric($data['service'])) {
            throw new Exception('Service ID must be numeric');
        }

        if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid link URL format');
        }
    }

    /**
     * Log debug information
     *
     * @param string $message
     * @param mixed $data
     * @return void
     */
    private function log(string $message, $data = null): void
    {
        if (!$this->debug) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";

        if ($data !== null) {
            $logMessage .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        error_log($logMessage);
    }
}
