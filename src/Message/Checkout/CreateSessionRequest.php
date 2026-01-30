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
                'value' => $this->getAmountInteger() ?? 0,
                'currency' => $this->getCurrency(),
            ],
            'merchantAccount' => $this->getMerchantAccount(),
            'reference' => $this->getTransactionId(),
            'returnUrl' => $this->getReturnUrl(),
            'countryCode' => $this->getCountryCode(),
        ];

        $data = $this->applyStorageParameters($data);

        if (!empty($this->getShopperInteraction())) {
            $data['shopperInteraction'] = $this->getShopperInteraction();
        }

        if (!empty($this->getRecurringProcessingModel())) {
            $data['recurringProcessingModel'] = $this->getRecurringProcessingModel();
        }

        if (!empty($this->getShopperReference())) {
            $data['shopperReference'] = $this->getShopperReference();
        }

        // Merge in any preserved parameters that have been explicitly allowed
        $data = $this->mergePreservedParameters($data);

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

    public function setStorePaymentMethodMode($storePaymentMethodMode)
    {
        $this->setParameter('storePaymentMethodMode', $storePaymentMethodMode);
    }
    public function getStorePaymentMethodMode()
    {
        return $this->getParameter('storePaymentMethodMode');
    }

    private function applyStorageParameters(array $data): array
    {
        // If the new mode is set, use it and ignore the old boolean
        if ($mode = $this->getStorePaymentMethodMode()) {
            $data['storePaymentMethodMode'] = $mode;
            return $data;
        }

        // Fallback to the legacy boolean for older versions
        if ($store = $this->getStorePaymentMethod()) {
            $data['storePaymentMethod'] = $store;
        }

        return $data;
    }
}
