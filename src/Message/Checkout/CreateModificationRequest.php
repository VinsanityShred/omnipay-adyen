<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Message\AbstractCheckoutRequest;
use Omnipay\Adyen\Message\AbstractRequest;

class CreateModificationRequest extends AbstractCheckoutRequest
{

    public function createResponse($data)
    {
        return new CreateModificationResponse($this, $data);
    }

    public function getEndpoint($parameters = null)
    {
        $endpoint = $this->getCheckoutUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_MODIFICATIONS
        );

        foreach ($parameters as $name => $value) {
            if (gettype($value) === 'string') {
                $endpoint = str_replace('{'.$name.'}', $value, $endpoint);
            }
        }

        return $endpoint;
    }

    public function getData()
    {
        $this->validate(
            'merchantAccount',
            'amount',
            'currency',
            'transactionId',
            'modificationAction'
        );

        $data = [
            'amount' => [
                'value' => $this->getAmountInteger(),
                'currency' => $this->getCurrency(),
            ],
            'merchantAccount' => $this->getMerchantAccount(),
            'reference' => $this->getTransactionId(),
            'paymentPspReference' => $this->getTransactionReference(),
            'modificationAction' => $this->getModificationAction(),
        ];

        return $data;
    }
    public function setModificationAction($modificationAction)
    {
        $this->setParameter('modificationAction', $modificationAction);
    }
    public function getModificationAction()
    {
        return $this->getParameter('modificationAction');
    }

}
