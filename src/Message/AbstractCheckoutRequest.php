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
        $headers = [
            'Content-Type' => 'application/json',
            // API Key header.
            'x-api-key' => $this->getApiKey(),
        ];
        $body = json_encode($data);

        // Log the outgoing request
        $this->logRequest('POST', $endpoint, $headers, $body);

        try {
            $response = $this->httpClient->request('POST', $endpoint, $headers, $body);
            $payload = $this->getJsonData($response);

            // Log the response
            $this->logResponse($endpoint, $response, $payload);

            return $this->createResponse($payload);
        } catch (\Throwable $e) {
            // Log any errors
            $this->logError($endpoint, $e);
            throw $e;
        }
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
