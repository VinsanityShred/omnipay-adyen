<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class CreateSessionResponse extends AbstractResponse
{
    use DataWalker;

    public function isSuccessful()
    {
        return count($this->getData()) > 0;
    }

    public function getSessionId()
    {
        return $this->getDataItem('id');
    }

    public function getSessionData()
    {
        return $this->getDataItem('sessionData');
    }

    public function getExpiresAt()
    {
        return $this->getDataItem('expiresAt');
    }

    public function getSession()
    {
        return [
            'id' => $this->getSessionId(),
            'sessionData' => $this->getSessionData(),
            'expiresAt' => $this->getExpiresAt(),
        ];
    }

}
