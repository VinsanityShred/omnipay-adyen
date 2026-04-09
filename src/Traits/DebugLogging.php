<?php

namespace Omnipay\Adyen\Traits;

use Psr\Http\Message\ResponseInterface;

/**
 * Trait for debug logging of Adyen API requests and responses.
 *
 * This allows userland code to enable logging to validate exactly what
 * was sent to Adyen and what they responded with.
 *
 * Usage with a closure:
 *   $gateway->setDebugMode(true);
 *   $gateway->setDebugLogger(function (string $message, array $context) {
 *       Log::debug($message, $context);
 *   });
 *
 * Usage with a PSR-3 logger:
 *   $gateway->setDebugMode(true);
 *   $gateway->setDebugLogger(function (string $message, array $context) use ($logger) {
 *       $logger->debug($message, $context);
 *   });
 *
 * The logger callable receives:
 *   - $message: A descriptive message like "Adyen API Request" or "Adyen API Response"
 *   - $context: An array with details like 'endpoint', 'method', 'headers', 'body', 'status_code', etc.
 */
trait DebugLogging
{
    /**
     * Get whether debug mode is enabled.
     *
     * @return bool
     */
    public function getDebugMode(): bool
    {
        return (bool) $this->getParameter('debug_mode');
    }

    /**
     * Enable or disable debug mode.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setDebugMode(bool $enabled)
    {
        return $this->setParameter('debug_mode', $enabled);
    }

    /**
     * Get the debug logger callable.
     *
     * @return callable|null
     */
    public function getDebugLogger(): ?callable
    {
        return $this->getParameter('debug_logger');
    }

    /**
     * Set the debug logger callable.
     *
     * The callable should accept two parameters:
     *   - string $message: The log message
     *   - array $context: Context data (endpoint, headers, body, etc.)
     *
     * @param callable|null $logger
     * @return $this
     */
    public function setDebugLogger(?callable $logger)
    {
        return $this->setParameter('debug_logger', $logger);
    }

    /**
     * Log a debug message if debug mode is enabled and a logger is configured.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function debugLog(string $message, array $context = []): void
    {
        if (!$this->getDebugMode()) {
            return;
        }

        $logger = $this->getDebugLogger();

        if ($logger === null) {
            return;
        }

        call_user_func($logger, $message, $context);
    }

    /**
     * Log an outgoing API request.
     *
     * @param string $method HTTP method (POST, GET, etc.)
     * @param string $endpoint The API endpoint URL
     * @param array $headers Request headers (sensitive values will be masked)
     * @param mixed $body The request body (will be decoded if JSON string)
     * @return void
     */
    protected function logRequest(string $method, string $endpoint, array $headers, $body): void
    {
        $context = [
            'direction' => 'request',
            'method' => $method,
            'endpoint' => $endpoint,
            'headers' => $this->maskSensitiveHeaders($headers),
            'body' => $this->formatBody($body),
            'timestamp' => date('c'),
        ];

        $this->debugLog('Adyen API Request', $context);
    }

    /**
     * Log an incoming API response.
     *
     * @param string $endpoint The API endpoint URL
     * @param ResponseInterface $response The PSR-7 response object
     * @param mixed $payload The decoded response payload
     * @return void
     */
    protected function logResponse(string $endpoint, ResponseInterface $response, $payload): void
    {
        $context = [
            'direction' => 'response',
            'endpoint' => $endpoint,
            'status_code' => $response->getStatusCode(),
            'reason_phrase' => $response->getReasonPhrase(),
            'headers' => $response->getHeaders(),
            'body' => $this->maskSensitiveData($payload),
            'timestamp' => date('c'),
        ];

        $this->debugLog('Adyen API Response', $context);
    }

    /**
     * Log an error during API communication.
     *
     * @param string $endpoint The API endpoint URL
     * @param \Throwable $exception The exception that occurred
     * @return void
     */
    protected function logError(string $endpoint, \Throwable $exception): void
    {
        $context = [
            'direction' => 'error',
            'endpoint' => $endpoint,
            'error_class' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'timestamp' => date('c'),
        ];

        $this->debugLog('Adyen API Error', $context);
    }

    /**
     * Mask sensitive values in headers.
     *
     * @param array $headers
     * @return array
     */
    protected function maskSensitiveHeaders(array $headers): array
    {
        $sensitiveHeaders = ['Authorization', 'authorization', 'x-api-key', 'X-API-Key'];
        $masked = [];

        foreach ($headers as $name => $value) {
            if (in_array($name, $sensitiveHeaders, true)) {
                // Show first and last few characters for debugging
                $valueStr = is_array($value) ? implode(', ', $value) : $value;
                if (strlen($valueStr) > 12) {
                    $masked[$name] = substr($valueStr, 0, 6) . '***' . substr($valueStr, -4);
                } else {
                    $masked[$name] = str_repeat('*', strlen($valueStr));
                }
            } else {
                $masked[$name] = $value;
            }
        }

        return $masked;
    }

    /**
     * Format the request body for logging.
     *
     * @param mixed $body
     * @return mixed
     */
    protected function formatBody($body)
    {
        // If it's a JSON string, decode it for better readability
        if (is_string($body)) {
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->maskSensitiveData($decoded);
            }
        }

        if (is_array($body)) {
            return $this->maskSensitiveData($body);
        }

        return $body;
    }

    /**
     * Mask sensitive data in the payload.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function maskSensitiveData($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $sensitiveKeys = [
            'number', 'cardNumber', 'encryptedCardNumber',
            'cvc', 'cvv', 'encryptedSecurityCode',
            'expiryMonth', 'expiryYear', 'encryptedExpiryMonth', 'encryptedExpiryYear',
            'password', 'secret',
        ];

        $masked = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveKeys, true)) {
                if (is_string($value) && strlen($value) > 4) {
                    $masked[$key] = str_repeat('*', strlen($value) - 4) . substr($value, -4);
                } else {
                    $masked[$key] = str_repeat('*', strlen($value));
                }
            } elseif (is_array($value)) {
                $masked[$key] = $this->maskSensitiveData($value);
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }
}
