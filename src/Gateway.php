<?php

namespace Omnipay\PayZone;

use Omnipay\Common\AbstractGateway;

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
            'merchantPassword' => '',
            'preSharedKey' => '',
            'secretKey' => ''
        ];
    }
}