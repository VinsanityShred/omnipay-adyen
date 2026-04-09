<?php

namespace Omnipay\Adyen\Message;

/**
 *
 */

//use Omnipay\Common\Exception\InvalidRequestException;

abstract class AbstractHppRequest extends AbstractRequest
{
    /**
     * Send the request with specified data.
     * This is a URL encoded POST with a JSON response.
     * The POST could be done from teh client, so all parameters
     * are signed. These must be signed in getData()
     *
     * @param  array $data The data to send
     * @return Omnipay\Common\Message\ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $endpoint = $this->getEndpoint();
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $body = http_build_query($data);

        // Log the outgoing request
        $this->logRequest('POST', $endpoint, $headers, $data);

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
     * Construct the string for signing from the data.
     * @return string
     */
    public function getSigningString(array $data)
    {
        // Sort the array by key using SORT_STRING order

        ksort($data, SORT_STRING);

        // Generate the signing data string.
        // The string contains a list of the field names, followed by
        // a list of the field values, all separated by colons (:).
        // Any colons in a field value must be escaped with a back-slash ("\")
        // When using SHA256 ALL sumbitted fields must be included.

        return implode(':', array_map(function ($val) {
            return str_replace(':', '\\:', str_replace('\\', '\\\\', $val));
        }, array_merge(array_keys($data), array_values($data))));
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->getParameter('customerId');
    }

    public function setCustomerId($value)
    {
        return $this->setParameter('customerId', $value);
    }
}
