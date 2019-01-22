<?php

namespace Omnipay\PayZone;

use Omnipay\Common\AbstractGateway;
use Omnipay\PayZone\Message\DummyCompletePurchase;

class Gateway extends AbstractGateway
{
    public function getName()
    {
        return "PayZone";
    }

    public function getDefaultParameters()
    {
        return [
            'merchantId' => '',
            'password' => '',
            'preSharedKey' => '',
        ];
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getPreSharedKey()
    {
        return $this->getParameter('preSharedKey');
    }

    public function setPreSharedKey($value)
    {
        return $this->setParameter('preSharedKey', $value);
    }

    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PayZone\Message\PurchaseRequest', $parameters);
    }

    public function completePurchase(array $parameters = [])
    {
        $parameters['PreSharedKey'] = $this->getPreSharedKey();
        $parameters['Password'] = $this->getPassword();
        $parameters['MerchantID'] = $this->getMerchantId();

        return new DummyCompletePurchase($this->httpClient, $this->httpRequest, $parameters);
    }
}
