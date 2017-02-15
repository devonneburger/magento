<?php
// app/code/local/Envato/Custompaymentmethod/Helper/Data.php
class Eftsecure_Custompaymentmethod_Helper_Data extends Mage_Core_Helper_Abstract
{
  function getPaymentGatewayUrl() 
  {
    return Mage::getUrl('custompaymentmethod/payment/gateway', array('_secure' => false));
  }
}