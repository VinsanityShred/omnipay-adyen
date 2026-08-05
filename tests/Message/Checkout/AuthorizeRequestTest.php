<?php

namespace Omnipay\Adyen\Tests\Message\Checkout;

use Omnipay\Adyen\Message\AbstractRequest;
use Omnipay\Adyen\Message\Checkout\AuthorizeRequest;
use Omnipay\Common\CreditCard;
use PHPUnit\Framework\TestCase;
use Omnipay\Common\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class AuthorizeRequestTest extends TestCase
{
    /** @var AuthorizeRequest */
    protected $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new AuthorizeRequest($httpClient, $httpRequest);

        $this->request->initialize([
            'amount' => '10.00',
            'currency' => 'EUR',
            'merchantAccount' => 'merchantAccount',
            'transactionId' => 'tx123',
            'paymentMethod' => [
                'type' => 'scheme',
                'encryptedCardNumber' => 'test_encrypted_card',
            ],
        ]);
    }

    public function testGetDataOmitsExecuteThreeDAtCurrentCheckoutVersion()
    {
        $this->request->set3DSecure(true);

        $data = $this->request->getData();

        $this->assertFalse(
            $this->request->isVersionAtOrBelow(AbstractRequest::VERSION_CHECKOUT, 69)
        );

        if (isset($data['additionalData'])) {
            $this->assertArrayNotHasKey('executeThreeD', $data['additionalData']);
        }
    }

    public function testGetDataOmitsAdditionalDataWhenOnlyThreeDSecureWouldPopulateIt()
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $request = new AuthorizeRequest($httpClient, $httpRequest);
        $request->initialize([
            'amount' => '10.00',
            'currency' => 'EUR',
            'merchantAccount' => 'merchantAccount',
            'transactionId' => 'tx123',
            'paymentMethod' => [
                'type' => 'scheme',
                'encryptedCardNumber' => 'test_encrypted_card',
            ],
            '3DSecure' => true,
        ]);

        $data = $request->getData();

        $this->assertArrayNotHasKey('additionalData', $data);
    }

    public function testGetDataIncludesBillingAddressWithoutExecuteThreeD()
    {
        $card = new CreditCard([
            'billingCity' => 'Amsterdam',
            'billingCountry' => 'NL',
            'billingAddress1' => '123',
            'billingAddress2' => 'Main Street',
            'billingPostcode' => '1011AA',
            'billingState' => 'NH',
        ]);

        $this->request->setCard($card);
        $this->request->set3DSecure(true);

        $data = $this->request->getData();

        $this->assertArrayHasKey('additionalData', $data);
        $this->assertArrayHasKey('billingAddress', $data['additionalData']);
        $this->assertArrayNotHasKey('executeThreeD', $data['additionalData']);
    }

    public function testExecuteThreeDWouldBeIncludedForCheckoutV69()
    {
        $this->assertTrue($this->request->isVersionAtOrBelow('v69', 69));
    }
}
