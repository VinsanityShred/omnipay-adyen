<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class PaymentMethodResponse extends AbstractResponse
{
    use DataWalker;

    public function isSuccessful()
    {
        return !$this->getMessage()
            && count($this->getData()) > 0;
    }

    public function getPaymentMethodsResponse()
    {
        return json_encode($this->data);
    }

    public function getMessage()
    {
        return isset($this->getData()['message'])
            ? $this->getData()['message']
            : null;
    }
}
