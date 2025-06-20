<?php

namespace Omnipay\Adyen\Traits;

trait GatewayParameters
{
    /**
     * @return string|null
     */
    public function getMerchantAccount()
    {
        return $this->getParameter('merchantAccount');
    }

    public function setMerchantAccount($value)
    {
        return $this->setParameter('merchantAccount', $value);
    }

    /**
     * @return string|null
     */
    public function getSkinCode()
    {
        return $this->getParameter('skinCode');
    }

    public function setSkinCode($value)
    {
        return $this->setParameter('skinCode', $value);
    }

    /**
     * @return string|null
     */
    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    public function setSecret($value)
    {
        return $this->setParameter('secret', $value);
    }

    /**
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Will be a hex string set in
     * Settings -> Server Communication -> Standard Notification
     * @return string|null
     */
    public function getNotificationSecret()
    {
        return $this->getParameter('notificationSecret');
    }

    public function setNotificationSecret($value)
    {
        return $this->setParameter('notificationSecret', $value);
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->getParameter('countryCode');
    }

    public function setCountryCode($value)
    {
        return $this->setParameter('countryCode', $value);
    }

    /**
     * @return string|null
     */
    public function getShopperLocale()
    {
        return $this->getParameter('shopperLocale');
    }

    public function setShopperLocale($value)
    {
        return $this->setParameter('shopperLocale', $value);
    }

    /**
     * @return string|null
     */
    public function getPublicKeyToken()
    {
        return $this->getParameter('publicKeyToken');
    }

    public function setPublicKeyToken($value)
    {
        return $this->setParameter('publicKeyToken', $value);
    }

    /**
     * @return string|null
     */
    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * @return string|null Any value that will be cast to bool in use.
     */
    public function get3DSecure()
    {
        return $this->getParameter('3DSecure');
    }

    public function set3DSecure($value)
    {
        return $this->setParameter('3DSecure', $value);
    }

    /**
     * @return string|null A comma-separated list of allowed payment methods.
     */
    public function getAllowedMethods()
    {
        return $this->getParameter('allowedMethods');
    }

    public function setAllowedMethods($value)
    {
        // If an array is passed in, then compact it to a string.

        if (is_array($value)) {
            $value = implode(',', array_unique($value));
        }

        return $this->setParameter('allowedMethods', $value);
    }

    /**
     * Add a single allowed method.
     */
    public function setAllowedMethod($value)
    {
        $methods = $this->getAllowedMethods();

        if ($methods === null || $methods === '') {
            $methods = $value;
        } else {
            $methods = explode(',', $methods);
            $methods[] = $value;
        }

        return $this->setAllowedMethods($methods);
    }

    /**
     * @return string|null A comma-separated list of blocked payment methods.
     */
    public function getBlockedMethods()
    {
        return $this->getParameter('blockedMethods');
    }

    public function setBlockedMethods($value)
    {
        // If an array is passed in, then compact it to a string.

        if (is_array($value)) {
            $value = implode(',', array_unique($value));
        }

        return $this->setParameter('blockedMethods', $value);
    }

    /**
     * Add a single blocked method.
     */
    public function setBlockedMethod($value)
    {
        $methods = $this->getBlockedMethods();

        if ($methods === null || $methods === '') {
            $methods = $value;
        } else {
            $methods = explode(',', $methods);
            $methods[] = $value;
        }

        return $this->setBlockedMethods($methods);
    }

    /**
     * @return string|null
     */
    public function getChannel()
    {
        return $this->getParameter('channel');
    }

    public function setChannel($value)
    {
        return $this->setParameter('channel', $value);
    }

    public function setShopperReference($shopperReference)
    {
        $this->setParameter('shopperReference', $shopperReference);
    }

    public function getShopperReference()
    {
        return $this->getParameter('shopperReference');
    }

    public function setBrowserInfo($browserInfo)
    {
        $this->setParameter('browserInfo', $browserInfo);
    }

    public function getBrowserInfo()
    {
        return $this->getParameter('browserInfo');
    }

    public function setOrigin($origin)
    {
        $this->setParameter('origin', $origin);
    }

    public function getOrigin()
    {
        return $this->getParameter('origin');
    }

    public function getMerchantAccountId()
    {
        return $this->getParameter('merchantAccountId');
    }

    public function setMerchantAccountId($value)
    {
        return $this->setParameter('merchantAccountId', $value);
    }

    /**
     * Get the live environment prefix.
     *
     * @return string
     */
    public function getLivePrefix()
    {
        return $this->getParameter('livePrefix');
    }

    /**
     * Set the live environment prefix.
     *
     * @param string $value
     * @return $this
     */
    public function setLivePrefix($value)
    {
        return $this->setParameter('livePrefix', $value);
    }

    /**
     * Get the live environment instance.
     *
     * @return string
     */
    public function getLiveInstance()
    {
        return $this->getParameter('liveInstance');
    }

    /**
     * Set the live environment instance.
     *
     * @param string $value
     * @return $this
     */
    public function setLiveInstance($value)
    {
        return $this->setParameter('liveInstance', $value);
    }
}
