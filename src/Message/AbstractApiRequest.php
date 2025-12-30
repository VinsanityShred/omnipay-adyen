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
        $auth = $this->getUsername() . ':' . $this->getPassword();
        $endpoint = $this->getEndpoint();
        $headers = [
            'Content-Type' => 'application/json',
            // Basic auth header.
            'Authorization' => 'Basic ' . base64_encode($auth)
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
