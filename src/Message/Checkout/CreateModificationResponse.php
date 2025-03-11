<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Traits\DataWalker;
use Omnipay\Common\Message\AbstractResponse;

class CreateModificationResponse extends AbstractResponse
{
    use DataWalker;

    protected $payload;

    public function isSuccessful()
    {
        return (isset($this->data['status']) && $this->data['status'] == 'received');
    }

    public function getData()
    {
        if ($this->payload === null) {
            $this->payload = parent::getData();

            if (is_array($this->payload)) {
                $this->payload = $this->expandKeys($this->payload);
            }

        }

        return $this->payload;
    }


}
