<?php

namespace Omnipay\Adyen\Tests\Traits;

use Omnipay\Adyen\Message\AbstractRequest;
use PHPUnit\Framework\TestCase;
use Omnipay\Common\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Concrete implementation of AbstractRequest for testing
 */
class TestableRequest extends AbstractRequest
{
    public function getData()
    {
        $data = [
            'merchantAccount' => $this->getMerchantAccount(),
            'amount' => $this->getAmountInteger(),
        ];

        // Merge preserved parameters like the real request classes do
        $data = $this->mergePreservedParameters($data);

        return $data;
    }

    public function sendData($data)
    {
        return null;
    }
}

class PreservedParametersTest extends TestCase
{
    protected TestableRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new TestableRequest($httpClient, $httpRequest);
    }

    public function test_setPreservedParameterKeys_stores_keys()
    {
        $keys = ['lineItems', 'metadata', 'applicationInfo'];

        $result = $this->request->setPreservedParameterKeys($keys);

        $this->assertSame($this->request, $result); // Should return $this for chaining
        $this->assertEquals($keys, $this->request->getPreservedParameterKeys());
    }

    public function test_getPreservedParameterKeys_returns_empty_array_by_default()
    {
        $this->assertEquals([], $this->request->getPreservedParameterKeys());
    }

    public function test_addPreservedParameterKey_adds_single_key()
    {
        $this->request->addPreservedParameterKey('lineItems');
        $this->request->addPreservedParameterKey('metadata');

        $this->assertEquals(['lineItems', 'metadata'], $this->request->getPreservedParameterKeys());
    }

    public function test_addPreservedParameterKey_does_not_duplicate_keys()
    {
        $this->request->addPreservedParameterKey('lineItems');
        $this->request->addPreservedParameterKey('lineItems');
        $this->request->addPreservedParameterKey('metadata');
        $this->request->addPreservedParameterKey('lineItems');

        $this->assertEquals(['lineItems', 'metadata'], $this->request->getPreservedParameterKeys());
    }

    public function test_removePreservedParameterKey_removes_key()
    {
        $this->request->setPreservedParameterKeys(['lineItems', 'metadata', 'applicationInfo']);

        $result = $this->request->removePreservedParameterKey('metadata');

        $this->assertSame($this->request, $result); // Should return $this for chaining
        $this->assertEquals(['lineItems', 'applicationInfo'], $this->request->getPreservedParameterKeys());
    }

    public function test_removePreservedParameterKey_handles_nonexistent_key()
    {
        $this->request->setPreservedParameterKeys(['lineItems', 'metadata']);

        $this->request->removePreservedParameterKey('nonexistent');

        $this->assertEquals(['lineItems', 'metadata'], $this->request->getPreservedParameterKeys());
    }

    public function test_getPreservedParametersData_returns_set_parameters()
    {
        $lineItems = [
            ['description' => 'Product 1', 'quantity' => 1],
            ['description' => 'Product 2', 'quantity' => 2],
        ];
        $metadata = ['orderId' => '12345'];

        $this->request->setPreservedParameterKeys(['lineItems', 'metadata', 'applicationInfo']);
        $this->request->setParameter('lineItems', $lineItems);
        $this->request->setParameter('metadata', $metadata);
        // applicationInfo is not set, so it should be excluded

        $data = $this->request->getPreservedParametersData();

        $this->assertEquals([
            'lineItems' => $lineItems,
            'metadata' => $metadata,
        ], $data);
    }

    public function test_getPreservedParametersData_excludes_null_values()
    {
        $this->request->setPreservedParameterKeys(['lineItems', 'metadata']);
        $this->request->setParameter('lineItems', null);
        $this->request->setParameter('metadata', ['key' => 'value']);

        $data = $this->request->getPreservedParametersData();

        $this->assertEquals([
            'metadata' => ['key' => 'value'],
        ], $data);
    }

    public function test_mergePreservedParameters_adds_to_data_array()
    {
        $lineItems = [['description' => 'Product 1']];

        $this->request->setPreservedParameterKeys(['lineItems']);
        $this->request->setParameter('lineItems', $lineItems);

        $existingData = ['merchantAccount' => 'TestMerchant', 'amount' => 1000];

        $result = $this->request->mergePreservedParameters($existingData);

        $this->assertEquals([
            'merchantAccount' => 'TestMerchant',
            'amount' => 1000,
            'lineItems' => $lineItems,
        ], $result);
    }

    public function test_mergePreservedParameters_does_not_override_existing_keys()
    {
        $this->request->setPreservedParameterKeys(['amount', 'lineItems']);
        $this->request->setParameter('amount', 9999); // Try to override
        $this->request->setParameter('lineItems', [['item' => 1]]);

        $existingData = ['merchantAccount' => 'TestMerchant', 'amount' => 1000];

        $result = $this->request->mergePreservedParameters($existingData);

        // amount should NOT be overridden
        $this->assertEquals(1000, $result['amount']);
        // lineItems should be added
        $this->assertEquals([['item' => 1]], $result['lineItems']);
    }

    public function test_getData_includes_preserved_parameters()
    {
        $lineItems = [['description' => 'Test Product', 'quantity' => 1]];

        $this->request->setMerchantAccount('TestMerchant');
        $this->request->setAmount('10.00');
        $this->request->setPreservedParameterKeys(['lineItems']);
        $this->request->setParameter('lineItems', $lineItems);

        $data = $this->request->getData();

        $this->assertEquals('TestMerchant', $data['merchantAccount']);
        $this->assertEquals(1000, $data['amount']);
        $this->assertEquals($lineItems, $data['lineItems']);
    }

    public function test_preserved_parameters_support_nested_arrays()
    {
        $complexData = [
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                    'items' => [1, 2, 3],
                ],
            ],
        ];

        $this->request->setPreservedParameterKeys(['complexData']);
        $this->request->setParameter('complexData', $complexData);

        $data = $this->request->getPreservedParametersData();

        $this->assertEquals(['complexData' => $complexData], $data);
    }

    public function test_preserved_parameters_empty_when_no_keys_set()
    {
        $this->request->setParameter('lineItems', [['item' => 1]]);
        $this->request->setParameter('metadata', ['key' => 'value']);

        // No preserved parameter keys set
        $data = $this->request->getPreservedParametersData();

        $this->assertEquals([], $data);
    }
}

