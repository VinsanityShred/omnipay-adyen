<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class CreateCardResponse extends AbstractResponse
{
    use DataWalker;

    public function isSuccessful()
    {
        return (isset($this->data['resultCode']) && $this->data['resultCode'] == 'Authorised');
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return $this->data['additionalData']['recurring.recurringDetailReference'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getDataItem('refusalReason');
    }
}
