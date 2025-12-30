<?php

namespace Omnipay\Adyen\Message;

abstract class AbstractCheckoutRequest extends AbstractApiRequest
{

    /**
     * Send the request with specified data.
     *
     * @param array $data The data to send
     * @return Omnipay\Common\Message\ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        [$endpoint, $data] = $this->buildEndpointCleanData($data);
        $response = $this->httpClient->request(
            'POST',
            $endpoint,
            [
                'Content-Type' => 'application/json',
                // API Key header.
                'x-api-key' => $this->getApiKey(),
            ],
            json_encode($data)
        );

        $payload = $this->getJsonData($response);

        return $this->createResponse($payload);
    }

    private function buildEndpointCleanData(array $parameters)
    {
        $endpoint = $this->getEndpoint($parameters);

        $keysToRemove = ['paymentPspReference', 'modificationAction'];

        if (isset($parameters['modificationAction']) && $parameters['modificationAction'] === 'reversals') {
            $keysToRemove[] = 'amount';
        }

        $parameters = array_filter($parameters, function($key) use ($keysToRemove) {
            return !in_array($key, $keysToRemove);
        }, ARRAY_FILTER_USE_KEY);

        return [$endpoint, $parameters];
    }

    /**
     * Add common optional parameters to the data array.
     * This method consolidates the duplicate parameter setting logic
     * used by both AuthorizeRequest and CreateCardRequest.
     *
     * @param array $data The data array to add parameters to
     * @return array The data array with optional parameters added
     */
    protected function addCommonOptionalParameters(array $data): array
    {
        if (!empty($this->getShopperReference())) {
            $data['shopperReference'] = $this->getShopperReference();
        }
        if (!empty($this->getReturnUrl())) {
            $data['returnUrl'] = $this->getReturnUrl();
        }
        if (!empty($this->getShopperName())) {
            $data['shopperName'] = $this->getShopperName();
        }
        if (!empty($this->getShopperEmail())) {
            $data['shopperEmail'] = $this->getShopperEmail();
        }
        if (!empty($this->getClientIp())) {
            $data['shopperIP'] = $this->getClientIp();
        }
        if (!empty($this->getBillingAddress())) {
            $data['billingAddress'] = $this->getBillingAddress();
        }
        if (!empty($this->getDeliveryAddress())) {
            $data['deliveryAddress'] = $this->getDeliveryAddress();
        }
        if (!empty($this->getBrowserInfo())) {
            $data['browserInfo'] = $this->getBrowserInfo();
        }
        if (!empty($this->getRiskData()) && !empty($this->getRiskData()['clientData'])) {
            $data['riskData']['clientData'] = $this->getRiskData()['clientData'];
        }

        // Merge in any preserved parameters that have been explicitly allowed
        $data = $this->mergePreservedParameters($data);

        return $data;
    }

    abstract public function createResponse($payload);

}
