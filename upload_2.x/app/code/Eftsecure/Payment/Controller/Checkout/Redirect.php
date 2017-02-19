<?php
namespace Eftsecure\Payment\Controller\Checkout;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
class Redirect extends \Magento\Framework\App\Action\Action
{
	protected $_checkoutSession;
	protected $_resultPageFactory;
	protected $_orderFactory;
	protected $_storeManager;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
		$this->_checkoutSession = $checkoutSession;
		$this->_resultPageFactory = $resultPageFactory;
		$this->_orderFactory = $orderFactory;
		$this->_storeManager = $storeManager;
        parent::__construct($context);
    }
	public function execute()
	{
		$last_order_id = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
		if($last_order_id){
			$order = $this->_orderFactory->create()->loadByIncrementId($last_order_id);
			$status = $order->getStatus();
			if($status != Order::STATE_CANCELED){
				$state = Order::STATE_CANCELED;
				$order->setStatus($state);
				$order->save();
			}
			$resultPage = $this->_resultPageFactory->create();
			return $resultPage;
		} else {
			$home_url = $this->_storeManager->getStore()->getBaseUrl();
			$this->_redirect($home_url);
		}
	}
}
?>