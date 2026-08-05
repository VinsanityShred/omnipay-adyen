<?php

namespace Omnipay\Adyen\Tests\Message\Api;

use Omnipay\Adyen\Message\AbstractRequest;
use Omnipay\Adyen\Message\Api\AuthorizeRequest;
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
            'card' => $this->createValidCard(),
        ]);
    }

    protected function createValidCard(): CreditCard
    {
        return new CreditCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'Test',
            'lastName' => 'User',
        ]);
    }

    public function testGetDataOmitsExecuteThreeDAtCurrentPaymentVersion()
    {
        $this->request->set3DSecure(true);

        $data = $this->request->getData();

        $this->assertFalse(
            $this->request->isVersionAtOrBelow(AbstractRequest::VERSION_PAYMENT_PAYMENT, 69)
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
            '3DSecure' => true,
        ]);

        $data = $request->getData();

        $this->assertArrayNotHasKey('additionalData', $data);
    }

    public function testGetDataIncludesBillingAddressWithoutExecuteThreeD()
    {
        $card = new CreditCard([
            'number' => '4111111111111111',
            'expiryMonth' => '12',
            'expiryYear' => '2030',
            'cvv' => '123',
            'firstName' => 'Test',
            'lastName' => 'User',
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

    public function testExecuteThreeDUsesPaymentApiVersionNotCheckoutVersion()
    {
        $this->assertFalse(
            $this->request->isVersionAtOrBelow(AbstractRequest::VERSION_PAYMENT_PAYMENT, 69)
        );
        $this->assertTrue($this->request->isVersionAtOrBelow('v69', 69));
    }

    /**
     * Test all the generated URLs in live mode.
     */
    public function testGeneratedLiveUrls()
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $request = new AuthorizeRequest($httpClient, $httpRequest);
        $request->setTestMode(false);

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payment/v71/authorise',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payment/v71/authorise3d',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE3D,
                $request::PAYMENT_GROUP_PAYMENT,
                $request::VERSION_PAYMENT_PAYMENT
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS,
                $request::PAYMENT_GROUP_RECURRING,
                $request::VERSION_PAYMENT_RECURRING
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getRecurringUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY,
                $request::PAYMENT_GROUP_PAYOUT,
                $request::VERSION_PAYMENT_PAYOUT
            )
        );

        $this->assertSame(
            'https://pal-live.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPayoutUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY
            )
        );

        $this->assertSame(
            'https://live.adyen.com/hpp/cse/js/token-token-token.shtml',
            $request->getCseUrl(
                'token-token-token'
            )
        );

        $this->assertSame(
            'https://live.adyen.com/hpp/directory/v2.shtml',
            $request->getDirectoryUrl()
        );

        $this->assertSame(
            'https://live.adyen.com/hpp/directory/v99.shtml',
            $request->getDirectoryUrl(
                'v99'
            )
        );
    }

    /**
     * Test all the generated URLs in test mode.
     */
    public function testGeneratedTestUrls()
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $request = new AuthorizeRequest($httpClient, $httpRequest);
        $request->setTestMode(true);

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payment/v71/authorise',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payment/v71/authorise3d',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYMENT_AUTHORISE3D,
                $request::PAYMENT_GROUP_PAYMENT,
                $request::VERSION_PAYMENT_PAYMENT
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS,
                $request::PAYMENT_GROUP_RECURRING,
                $request::VERSION_PAYMENT_RECURRING
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Recurring/v25/listRecurringDetails',
            $request->getRecurringUrl(
                $request::SERVICE_GROUP_RECURRING_LISTRECURRINGDETAILS
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPaymentUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY,
                $request::PAYMENT_GROUP_PAYOUT,
                $request::VERSION_PAYMENT_PAYOUT
            )
        );

        $this->assertSame(
            'https://pal-test.adyen.com/pal/servlet/Payout/v30/submitThirdParty',
            $request->getPayoutUrl(
                $request::SERVICE_GROUP_PAYOUT_SUBMITTHIRDPARTY
            )
        );

        $this->assertSame(
            'https://test.adyen.com/hpp/cse/js/token-token-token.shtml',
            $request->getCseUrl(
                'token-token-token'
            )
        );

        $this->assertSame(
            'https://test.adyen.com/hpp/directory/v2.shtml',
            $request->getDirectoryUrl()
        );

        $this->assertSame(
            'https://test.adyen.com/hpp/directory/v99.shtml',
            $request->getDirectoryUrl(
                'v99'
            )
        );
    }
}
