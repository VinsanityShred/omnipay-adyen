<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class CreateSessionResponse extends AbstractResponse
{
    use DataWalker;

    public function isSuccessful()
    {
        return !$this->getMessage()
            && $this->getSessionId() !== null
            && $this->getSessionData() !== null;
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

    public function getMessage()
    {
        return isset($this->getData()['message'])
            ? $this->getData()['message']
            : null;
    }

}
