<?php
namespace Eftsecure\Payment\Controller\Checkout;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
class Success extends \Magento\Framework\App\Action\Action
{
	protected $_checkoutSession;
	protected $_orderFactory;
	protected $_storeManager;
	protected $_scopeConfig;
	protected $_encryptor;
 
    public function __construct(Context $context, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Encryption\EncryptorInterface $encryptor)
    {
		$this->_checkoutSession = $checkoutSession;
		$this->_orderFactory = $orderFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_encryptor = $encryptor;
        parent::__construct($context);
    }
	public function execute()
	{
		if ($this->getRequest()->getPost("orderId") && $this->getRequest()->getPost("eftsecure_transaction_id")) {
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
				
			$token_data = json_decode($response);
			
			$gateway_reference = $this->getRequest()->getPost("eftsecure_transaction_id");
		
			$headers = array(
				'X-Token: '.$token_data->token,
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
				$orderId = $this->getRequest()->getPost("orderId");
				$order = $this->_orderFactory->create()->loadByIncrementId($orderId);
				$state = Order::STATE_PAYMENT_REVIEW;
				$order->setStatus($state);
				$order->save();
				$this->_redirect('checkout/onepage/success');
			} else {
				$this->_redirect('404');
			}
		   
		} else {
			$this->_redirect('404');
		}
	}
}
?>