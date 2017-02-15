<?php
class Eftsecure_Custompaymentmethod_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'custompaymentmethod';
  protected $_formBlockType = 'custompaymentmethod/form_custompaymentmethod';
  //protected $_infoBlockType = 'payment/info';
 
  public function getOrderPlaceRedirectUrl()
  {
	Mage::getSingleton('core/session')->setEftsecureFlag(1);
    return Mage::getUrl('custompaymentmethod/payment/redirect', array('_secure' => false));
  }
}