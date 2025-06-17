<?php

namespace Omnipay\Adyen\Message\Checkout;

use Omnipay\Adyen\Message\AbstractCheckoutRequest;
use Omnipay\Adyen\Message\AbstractRequest;

class CreateSessionRequest extends AbstractCheckoutRequest
{

    public function createResponse($data)
    {
        return new CreateSessionResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getCheckoutUrl(
            AbstractRequest::SERVICE_GROUP_PAYMENT_SESSIONS
        );
    }

    public function getData()
    {
        $this->validate(
            'merchantAccount',
            'currency',
            'transactionId',
            'returnUrl',
            'countryCode'
        );

        $data = [
            'amount' => [
                'value' => 0,
                'currency' => $this->getCurrency(),
            ],
            'merchantAccount' => $this->getMerchantAccount(),
            'reference' => $this->getTransactionId(),
            'returnUrl' => $this->getReturnUrl(),
            'countryCode' => $this->getCountryCode(),
        ];

        if (!empty($this->getStorePaymentMethod())) {
            $data['storePaymentMethod'] = $this->getStorePaymentMethod();
        }

        if (!empty($this->getShopperInteraction())) {
            $data['shopperInteraction'] = $this->getShopperInteraction();
        }

        if (!empty($this->getRecurringProcessingModel())) {
            $data['recurringProcessingModel'] = $this->getRecurringProcessingModel();
        }

        if (!empty($this->getShopperReference())) {
            $data['shopperReference'] = $this->getShopperReference();
        }

        return $data;
    }

    public function setStorePaymentMethod($storePaymentMethod)
    {
        $this->setParameter('storePaymentMethod', $storePaymentMethod);
    }
    public function getStorePaymentMethod()
    {
        return $this->getParameter('storePaymentMethod');
    }

    public function setShopperInteraction($shopperInteraction)
    {
        $this->setParameter('shopperInteraction', $shopperInteraction);
    }
    public function getShopperInteraction()
    {
        return $this->getParameter('shopperInteraction');
    }

    public function setRecurringProcessingModel($recurringProcessingModel)
    {
        $this->setParameter('recurringProcessingModel', $recurringProcessingModel);
    }
    public function getRecurringProcessingModel()
    {
        return $this->getParameter('recurringProcessingModel');
    }
}
