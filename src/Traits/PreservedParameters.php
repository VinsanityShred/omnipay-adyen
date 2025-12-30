<?php

namespace Omnipay\Adyen\Traits;

/**
 * Trait for handling additional parameters that should be preserved and passed through to the API.
 *
 * This allows userland code to pass extra parameters through to Adyen without requiring
 * modifications to the package for each new parameter.
 *
 * Usage:
 *   // First, declare which parameters should be preserved
 *   $gateway->setPreservedParameterKeys(['lineItems', 'metadata', 'applicationInfo']);
 *
 *   // Then, when making requests, those parameters will be included if set
 *   $gateway->session([
 *       'amount' => '10.00',
 *       'lineItems' => [...],  // This will be preserved
 *       'metadata' => [...],   // This will be preserved
 *   ]);
 */
trait PreservedParameters
{
    /**
     * Get the list of parameter keys that should be preserved.
     *
     * @return array
     */
    public function getPreservedParameterKeys(): array
    {
        return $this->getParameter('preservedParameterKeys') ?? [];
    }

    /**
     * Set the list of parameter keys that should be preserved.
     *
     * @param array $keys Array of parameter names to preserve
     * @return $this
     */
    public function setPreservedParameterKeys(array $keys)
    {
        return $this->setParameter('preservedParameterKeys', $keys);
    }

    /**
     * Add a single parameter key to the preserved list.
     *
     * @param string $key The parameter name to preserve
     * @return $this
     */
    public function addPreservedParameterKey(string $key)
    {
        $keys = $this->getPreservedParameterKeys();

        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
        }

        return $this->setPreservedParameterKeys($keys);
    }

    /**
     * Remove a parameter key from the preserved list.
     *
     * @param string $key The parameter name to remove
     * @return $this
     */
    public function removePreservedParameterKey(string $key)
    {
        $keys = $this->getPreservedParameterKeys();
        $keys = array_filter($keys, fn($k) => $k !== $key);

        return $this->setPreservedParameterKeys(array_values($keys));
    }

    /**
     * Get the data for all preserved parameters that have values.
     *
     * This method iterates through the preserved parameter keys and returns
     * an associative array of key => value for all parameters that have been set.
     *
     * @return array
     */
    public function getPreservedParametersData(): array
    {
        $data = [];

        foreach ($this->getPreservedParameterKeys() as $key) {
            $value = $this->getParameter($key);

            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Merge preserved parameters into an existing data array.
     *
     * This is a convenience method for use in getData() implementations.
     * Preserved parameters will NOT override existing keys in the data array.
     *
     * @param array $data The existing data array
     * @return array The merged data array
     */
    public function mergePreservedParameters(array $data): array
    {
        $preserved = $this->getPreservedParametersData();

        // Only add preserved parameters that don't already exist in data
        foreach ($preserved as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
