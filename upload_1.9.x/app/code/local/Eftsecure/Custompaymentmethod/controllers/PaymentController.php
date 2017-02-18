<?php
class Eftsecure_Custompaymentmethod_PaymentController extends Mage_Core_Controller_Front_Action 
{
  public function gatewayAction() 
  {
    if ($this->getRequest()->get("orderId"))
    {
      $arr_querystring = array(
        'flag' => 1, 
        'orderId' => $this->getRequest()->get("orderId"),
        'eftsecure_transaction_id' => $this->getRequest()->get("eftsecure_transaction_id"),
      );
       
      Mage_Core_Controller_Varien_Action::_redirect('custompaymentmethod/payment/response', array('_secure' => false, '_query'=> $arr_querystring));
    }
  }
   
  public function redirectAction() 
  {
	$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
	$EftsecureFlag = Mage::getSingleton('core/session')->getEftsecureFlag();
	if($orderId && $EftsecureFlag == 1){
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$order_status = $order->getStatusLabel();
		if(strtolower($order_status) != 'canceled'){
			$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Payment Unsuccessfull.');
			$order->save();
		}
		
		$eftsecure_username = Mage::getStoreConfig('payment/custompaymentmethod/eftsecure_username');
		$eftsecure_password = Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/custompaymentmethod/eftsecure_password'));
		$curl = curl_init('https://services.callpay.com/api/v1/token');
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_USERPWD, $eftsecure_username . ":" . $eftsecure_password);

		$response = curl_exec($curl);
		curl_close($curl);
			
		$response_data = json_decode($response);
		
		if(isset($response_data->token)){
			Mage::getSingleton('core/session')->setEftsecureToken($response_data->token);
			Mage::getSingleton('core/session')->setEftsecureOrganisation($response_data->organisation_id);
		} else {
			Mage::getSingleton('core/session')->setEftsecureToken('');
			Mage::getSingleton('core/session')->setEftsecureOrganisation('');
		}
		//Mage::getSingleton('core/session')->getEftsecureToken();
		//Mage::getSingleton('core/session')->unsEftsecureToken();
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('Mage_Core_Block_Template','custompaymentmethod',array('template' => 'custompaymentmethod/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	} else {
		 Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure'=> false));
	}
  }
 
  public function responseAction() {
    if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId") && $this->getRequest()->get("eftsecure_transaction_id")) {
		$gateway_reference = $this->getRequest()->get("eftsecure_transaction_id");
		
		$headers = array(
			'X-Token: '.Mage::getSingleton('core/session')->getEftsecureToken(),
		);
		
		$curl = curl_init('https://services.callpay.com/api/v1/gateway-transaction/'.$gateway_reference);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($curl);
		curl_close($curl);
		
		$response_data = json_decode($response);
		if($response_data->id == $gateway_reference && $response_data->successful == 1) {
			$orderId = $this->getRequest()->get("orderId");
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Payment Success.');
			$order->save();

			Mage::getSingleton('checkout/session')->unsQuoteId();
			Mage::getSingleton('core/session')->unsEftsecureFlag();
			Mage::getSingleton('core/session')->unsEftsecureToken();
			Mage::getSingleton('core/session')->unsEftsecureOrganisation();
			Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
		} else {
			Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure'=> false));
		}
    } else {
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure'=> false));
    }
  }
}