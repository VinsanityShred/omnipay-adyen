<?php

namespace Omnipay\Adyen;

use Omnipay\Common\AbstractGateway as CommonAbstractGateway;
use Omnipay\Adyen\Traits\GatewayParameters;
use Omnipay\Adyen\Message\FetchPaymentMethodsRequest;
use Omnipay\Adyen\Message\AuthorizeRequest;
use Omnipay\Adyen\Message\CompleteAuthorizeRequest;
use Omnipay\Adyen\Message\CseClientRequest;

abstract class AbstractGateway extends CommonAbstractGateway
{
    use GatewayParameters;

    /**
     *
     */
    public function getDefaultParameters()
    {
        return [
            'merchantAccount' => '',
            'skinCode' => null,
            'secret' => '',
            'apiKey' => '',
            'currency' => 'USD',
            'publicKeyToken' => '',             // publicKeyToken aka Library Token.
            'username' => '',
            'password' => '',
            'testMode' => true,
            'shopperLocale' => 'en_US',
            'countryCode' => 'us',
            'livePrefix' => '',
            'liveInstance' => 'live-us',
        ];
    }
}
