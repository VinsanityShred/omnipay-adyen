<?php

namespace Omnipay\Adyen\Tests\Message;

use Omnipay\Adyen\Message\AbstractRequest;
use PHPUnit\Framework\TestCase;
use Omnipay\Common\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class TestRequest extends AbstractRequest
{
    public function getData() { return []; }
    public function sendData($data) { return null; }
}

class AbstractRequestTest extends TestCase
{
    protected $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new TestRequest($httpClient, $httpRequest);
    }

    public function testGetCheckoutUrlTestMode()
    {
        $this->request->setTestMode(true);
        
        $url = $this->request->getCheckoutUrl('payments');
        
        $this->assertEquals(
            'https://checkout-test.adyen.com/v69/payments',
            $url
        );
    }

    public function testGetCheckoutUrlLiveMode()
    {
        $this->request->setTestMode(false);
        
        // Test with default values
        $url = $this->request->getCheckoutUrl('payments');
        
        $this->assertEquals(
            'https://checkout-live.adyenpayments.com/checkout/v69/payments',
            $url
        );

        // Test with custom prefix and instance
        $this->request->setLivePrefix('xxx-xxx');
        $this->request->setLiveInstance('custom');
        
        $url = $this->request->getCheckoutUrl('payments');
        
        $this->assertEquals(
            'https://xxx-xxx-checkout-custom.adyenpayments.com/checkout/v69/payments',
            $url
        );
    }

    public function testGetCheckoutUtilityUrlTestMode()
    {
        $this->request->setTestMode(true);
        
        $url = $this->request->getCheckoutUtilityUrl();
        
        $this->assertEquals(
            'https://checkout-test.adyen.com/v1',
            $url
        );
    }

    public function testGetCheckoutUtilityUrlLiveMode()
    {
        $this->request->setTestMode(false);
        
        // Test with default values
        $url = $this->request->getCheckoutUtilityUrl();
        
        $this->assertEquals(
            'https://checkout-live.adyenpayments.com/checkout/v1',
            $url
        );

        // Test with custom prefix and instance
        $this->request->setLivePrefix('xxx-xxx');
        $this->request->setLiveInstance('custom');
        
        $url = $this->request->getCheckoutUtilityUrl();
        
        $this->assertEquals(
            'https://xxx-xxx-checkout-custom.adyenpayments.com/checkout/v1',
            $url
        );
    }
} 