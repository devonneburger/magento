<?php

namespace Eftsecure\Payment\Model;

class Eftpay extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = 'eftpay';

    protected $_isOffline = true;

	public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
