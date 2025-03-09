<?php

namespace Omnipay\Adyen\Message;

/**
 *
 */

//use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractApiRequest extends AbstractRequest
{
    /**
     * Send the request with specified data.
     *
     * @param  array $data The data to send
     * @return Omnipay\Common\Message\ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $auth = ($this->getUsername() && $this->getPassword()) ? $this->getUsername() . ':' . $this->getPassword() : null;
        $apiKey = $this->getApiKey();

        $headers = [
            'Content-Type' => 'application/json',
        ];
        // Basic auth header.
        if ($auth) {
            $headers['Authorization'] = 'Basic ' . base64_encode($auth);
        }
        // API Key header.
        if ($apiKey) {
            $headers['X-API-Key']     = $apiKey;
        }

        $response = $this->httpClient->request(
            'POST',
            $this->getEndpoint(),
            $headers,
            json_encode($data)
        );

        $payload = $this->getJsonData($response);

        return $this->createResponse($payload);
    }

    /**
     * The common fields needed for all API requests.
     */
    public function getBaseData($data = [])
    {
        $this->validate('username', 'password', 'merchantAccount');

        $data['merchantAccount'] = $this->getMerchantAccount();

        return $data;
    }

    abstract public function createResponse($payload);
}
