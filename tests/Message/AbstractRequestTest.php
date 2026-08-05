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
            'https://checkout-test.adyen.com/' . AbstractRequest::VERSION_CHECKOUT . '/payments',
            $url
        );
    }

    public function testGetCheckoutUrlLiveMode()
    {
        $this->request->setTestMode(false);
        
        // Test with default values
        $url = $this->request->getCheckoutUrl('payments');
        
        $this->assertEquals(
            'https://checkout-live.adyenpayments.com/checkout/' . AbstractRequest::VERSION_CHECKOUT . '/payments',
            $url
        );

        // Test with custom prefix and instance
        $this->request->setLivePrefix('xxx-xxx');
        $this->request->setLiveInstance('custom');
        
        $url = $this->request->getCheckoutUrl('payments');
        
        $this->assertEquals(
            'https://xxx-xxx-checkout-custom.adyenpayments.com/checkout/' . AbstractRequest::VERSION_CHECKOUT . '/payments',
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

    /**
     * @dataProvider versionAtOrBelowProvider
     */
    public function testIsVersionAtOrBelow($versionString, $threshold, $expected)
    {
        $this->assertSame($expected, $this->request->isVersionAtOrBelow($versionString, $threshold));
    }

    public function versionAtOrBelowProvider()
    {
        return [
            'v69 at threshold 69' => ['v69', 69, true],
            'v68 below threshold 69' => ['v68', 69, true],
            'v71 above threshold 69' => ['v71', 69, false],
            'v70 above threshold 69' => ['v70', 69, false],
            'current checkout version above threshold' => [AbstractRequest::VERSION_CHECKOUT, 69, false],
            'current payment version above threshold' => [AbstractRequest::VERSION_PAYMENT_PAYMENT, 69, false],
            'malformed version without prefix' => ['71', 69, false],
            'empty version string' => ['', 69, false],
        ];
    }
} 