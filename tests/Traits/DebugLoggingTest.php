<?php

namespace Omnipay\Adyen\Tests\Traits;

use Omnipay\Adyen\Message\AbstractRequest;
use PHPUnit\Framework\TestCase;
use Omnipay\Common\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Concrete implementation of AbstractRequest for testing debug logging
 */
class TestableLoggingRequest extends AbstractRequest
{
    public function getData()
    {
        return [];
    }

    public function sendData($data)
    {
        return null;
    }

    // Expose protected methods for testing
    public function testLogRequest(string $method, string $endpoint, array $headers, $body): void
    {
        $this->logRequest($method, $endpoint, $headers, $body);
    }

    public function testDebugLog(string $message, array $context = []): void
    {
        $this->debugLog($message, $context);
    }

    public function testMaskSensitiveHeaders(array $headers): array
    {
        return $this->maskSensitiveHeaders($headers);
    }

    public function testMaskSensitiveData($data)
    {
        return $this->maskSensitiveData($data);
    }

    public function testFormatBody($body)
    {
        return $this->formatBody($body);
    }
}

class DebugLoggingTest extends TestCase
{
    protected TestableLoggingRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new TestableLoggingRequest($httpClient, $httpRequest);
    }

    public function test_setDebugMode_stores_value()
    {
        $result = $this->request->setDebugMode(true);

        $this->assertSame($this->request, $result); // Should return $this for chaining
        $this->assertTrue($this->request->getDebugMode());

        $this->request->setDebugMode(false);
        $this->assertFalse($this->request->getDebugMode());
    }

    public function test_getDebugMode_returns_false_by_default()
    {
        $this->assertFalse($this->request->getDebugMode());
    }

    public function test_setDebugLogger_stores_callable()
    {
        $logger = function (string $message, array $context) {
            // Test logger
        };

        $result = $this->request->setDebugLogger($logger);

        $this->assertSame($this->request, $result); // Should return $this for chaining
        $this->assertSame($logger, $this->request->getDebugLogger());
    }

    public function test_getDebugLogger_returns_null_by_default()
    {
        $this->assertNull($this->request->getDebugLogger());
    }

    public function test_debugLog_calls_logger_when_enabled()
    {
        $loggedMessages = [];

        $this->request->setDebugMode(true);
        $this->request->setDebugLogger(function (string $message, array $context) use (&$loggedMessages) {
            $loggedMessages[] = ['message' => $message, 'context' => $context];
        });

        $this->request->testDebugLog('Test message', ['key' => 'value']);

        $this->assertCount(1, $loggedMessages);
        $this->assertEquals('Test message', $loggedMessages[0]['message']);
        $this->assertEquals(['key' => 'value'], $loggedMessages[0]['context']);
    }

    public function test_debugLog_does_not_call_logger_when_disabled()
    {
        $loggedMessages = [];

        $this->request->setDebugMode(false);
        $this->request->setDebugLogger(function (string $message, array $context) use (&$loggedMessages) {
            $loggedMessages[] = ['message' => $message, 'context' => $context];
        });

        $this->request->testDebugLog('Test message', ['key' => 'value']);

        $this->assertCount(0, $loggedMessages);
    }

    public function test_debugLog_does_not_fail_without_logger()
    {
        $this->request->setDebugMode(true);
        // No logger set

        // Should not throw an exception
        $this->request->testDebugLog('Test message', ['key' => 'value']);

        $this->assertTrue(true); // If we get here, the test passed
    }

    public function test_logRequest_logs_correct_context()
    {
        $loggedMessages = [];

        $this->request->setDebugMode(true);
        $this->request->setDebugLogger(function (string $message, array $context) use (&$loggedMessages) {
            $loggedMessages[] = ['message' => $message, 'context' => $context];
        });

        $this->request->testLogRequest(
            'POST',
            'https://checkout-test.adyen.com/v69/payments',
            ['Content-Type' => 'application/json', 'x-api-key' => 'test_api_key_12345'],
            ['amount' => ['value' => 1000, 'currency' => 'USD']]
        );

        $this->assertCount(1, $loggedMessages);
        $this->assertEquals('Adyen API Request', $loggedMessages[0]['message']);
        $this->assertEquals('request', $loggedMessages[0]['context']['direction']);
        $this->assertEquals('POST', $loggedMessages[0]['context']['method']);
        $this->assertEquals('https://checkout-test.adyen.com/v69/payments', $loggedMessages[0]['context']['endpoint']);
        $this->assertArrayHasKey('timestamp', $loggedMessages[0]['context']);
    }

    public function test_maskSensitiveHeaders_masks_authorization()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic dXNlcm5hbWU6cGFzc3dvcmQ=',
            'x-api-key' => 'test_api_key_very_long_value',
        ];

        $masked = $this->request->testMaskSensitiveHeaders($headers);

        $this->assertEquals('application/json', $masked['Content-Type']);
        $this->assertNotEquals('Basic dXNlcm5hbWU6cGFzc3dvcmQ=', $masked['Authorization']);
        $this->assertStringContainsString('***', $masked['Authorization']);
        $this->assertNotEquals('test_api_key_very_long_value', $masked['x-api-key']);
        $this->assertStringContainsString('***', $masked['x-api-key']);
    }

    public function test_maskSensitiveHeaders_shows_partial_value()
    {
        $headers = [
            'Authorization' => 'Basic abcdefghijklmnop',
        ];

        $masked = $this->request->testMaskSensitiveHeaders($headers);

        // Should show first 6 and last 4 characters
        $this->assertEquals('Basic ***mnop', $masked['Authorization']);
    }

    public function test_maskSensitiveData_masks_card_number()
    {
        $data = [
            'paymentMethod' => [
                'type' => 'scheme',
                'number' => '4111111111111111',
                'cvc' => '123',
                'expiryMonth' => '03',
                'expiryYear' => '2030',
            ],
            'amount' => [
                'value' => 1000,
                'currency' => 'USD',
            ],
        ];

        $masked = $this->request->testMaskSensitiveData($data);

        // Card number should be masked
        $this->assertStringContainsString('1111', $masked['paymentMethod']['number']);
        $this->assertStringContainsString('*', $masked['paymentMethod']['number']);
        $this->assertNotEquals('4111111111111111', $masked['paymentMethod']['number']);

        // CVC should be masked
        $this->assertEquals('***', $masked['paymentMethod']['cvc']);

        // Expiry should be masked
        $this->assertStringContainsString('*', $masked['paymentMethod']['expiryMonth']);
        $this->assertStringContainsString('*', $masked['paymentMethod']['expiryYear']);

        // Amount should not be masked
        $this->assertEquals(1000, $masked['amount']['value']);
        $this->assertEquals('USD', $masked['amount']['currency']);
    }

    public function test_maskSensitiveData_handles_nested_arrays()
    {
        $data = [
            'level1' => [
                'level2' => [
                    'number' => '4111111111111111',
                    'safe' => 'visible',
                ],
            ],
        ];

        $masked = $this->request->testMaskSensitiveData($data);

        $this->assertNotEquals('4111111111111111', $masked['level1']['level2']['number']);
        $this->assertEquals('visible', $masked['level1']['level2']['safe']);
    }

    public function test_formatBody_decodes_json_string()
    {
        $jsonBody = '{"amount":{"value":1000,"currency":"USD"}}';

        $formatted = $this->request->testFormatBody($jsonBody);

        $this->assertIsArray($formatted);
        $this->assertEquals(1000, $formatted['amount']['value']);
        $this->assertEquals('USD', $formatted['amount']['currency']);
    }

    public function test_formatBody_returns_array_as_is()
    {
        $arrayBody = ['amount' => ['value' => 1000, 'currency' => 'USD']];

        $formatted = $this->request->testFormatBody($arrayBody);

        $this->assertEquals($arrayBody, $formatted);
    }

    public function test_formatBody_returns_non_json_string_as_is()
    {
        $nonJsonBody = 'key1=value1&key2=value2';

        $formatted = $this->request->testFormatBody($nonJsonBody);

        $this->assertEquals($nonJsonBody, $formatted);
    }

    public function test_formatBody_masks_sensitive_data_in_json()
    {
        $jsonBody = '{"paymentMethod":{"number":"4111111111111111"}}';

        $formatted = $this->request->testFormatBody($jsonBody);

        $this->assertNotEquals('4111111111111111', $formatted['paymentMethod']['number']);
    }
}

