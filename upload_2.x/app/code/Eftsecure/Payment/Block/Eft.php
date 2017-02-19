<?php
namespace Eftsecure\Payment\Block;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;
class Eft extends \Magento\Framework\View\Element\Template
{
	protected $_checkoutSession;
	protected $_orderFactory;
	protected $_scopeConfig;
	protected $_encryptor;
	
    public function __construct( \Magento\Framework\View\Element\Template\Context $context, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,  \Magento\Framework\Encryption\EncryptorInterface $encryptor)
    {
		$this->_checkoutSession = $checkoutSession;
		$this->_orderFactory = $orderFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_encryptor = $encryptor;
		parent::__construct($context);
    }
	
    public function getParameters()
    {
		$last_order_id = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
		if($last_order_id){
			$order = $this->_orderFactory->create()->loadByIncrementId($last_order_id);
			
			$instructions = $this->_scopeConfig->getValue('payment/eftpay/instructions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			
			$eftsecure_username = $this->_scopeConfig->getValue('payment/eftpay/eftsecure_username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$eftsecure_password = $this->_encryptor->decrypt($this->_scopeConfig->getValue('payment/eftpay/eftsecure_password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
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
				$eftsecure_params = array(
					"reference" 		=> 'order_'.$last_order_id,
					"last_order_id" 	=> $last_order_id,
					"organisation_id" 	=> $response_data->organisation_id,
					"token" 			=> $response_data->token,
					"amount" 			=> number_format($order->getGrandTotal(), 2),
					"instructions" 		=> $instructions,
					"pcolor" 			=> '',
					"scolor" 			=> '',
				);
			} else {
				$eftsecure_params = array();
			}
		} else {
			$eftsecure_params = array();
		}
		return $eftsecure_params;
    }
}
?>