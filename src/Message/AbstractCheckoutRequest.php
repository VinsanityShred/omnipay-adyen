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

        if ($parameters['modificationAction'] == 'reversals') {
            $keysToRemove[] = 'amount';
        }

        $parameters = array_filter($parameters, function($key) use ($keysToRemove) {
            return !in_array($key, $keysToRemove);
        }, ARRAY_FILTER_USE_KEY);

        return [$endpoint, $parameters];
    }

    abstract public function createResponse($payload);

}
