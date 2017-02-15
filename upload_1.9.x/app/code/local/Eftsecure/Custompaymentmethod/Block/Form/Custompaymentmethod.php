<?php
class Eftsecure_Custompaymentmethod_Block_Form_Custompaymentmethod extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('custompaymentmethod/form/info.phtml');
  }
}