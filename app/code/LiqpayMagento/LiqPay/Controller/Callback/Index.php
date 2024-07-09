<?php
namespace LiqpayMagento\LiqPay\Controller\Callback;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Sales\Model\Order;
use LiqpayMagento\LiqPay\Sdk\LiqPay;
use Psr\Log\LoggerInterface;

class Index extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    protected $_pageFactory;
    protected $_liqPay;
    protected $_logger;
    protected $_resultJsonFactory;
    protected $_order;
    
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        LiqPay $liqPay,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        Order $order
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_liqPay = $liqPay;
        $this->_logger = $logger;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_order = $order;
        parent::__construct($context);
    }
    
    public function execute()
    {
$this->_logger->info("Start of callback!");
        $request = $this->getRequest();
//	var_dump($request);
        $data = $request->getPost('data');
        $signature = $request->getPost('signature');
//        var_dump($data);
$this->_logger->info('Request data: ' . print_r($request->getPost(), true));
        if (empty($data) || empty($signature)) {
            $this->_logger->error('Missing required parameters.');
            die('Error: Missing required parameters.');
        }
        
        $decodedData = $this->_liqPay->getDecodedData($data);
        
        // Логування отриманих даних
//        $this->_logger->info('LiqPay Callback Data: ', $decodedData);
        $helper = $this->_liqPay->getHelper();
        $private = $helper->getPrivateKey();
        $this->_logger->info('Private key: ' . $private);
        $this->_logger->info('Signature: ' . $signature);
//        $this->_logger->info('Data: ' . $data);
        $signature2 = base64_encode(sha1($private . trim($data) . $private, 1));
        $this->_logger->info('Signature2: ' . $signature2);
        // Перевірка підпису
        if ($this->_liqPay->checkSignature($signature, $data)) {
            // Обробка статусу транзакції
            $status = $decodedData['status'];
            $orderId = $decodedData['order_id'];
            
            switch ($status) {
                case LiqPay::STATUS_SUCCESS:
                case LiqPay::STATUS_WAIT_COMPENSATION:
                case LiqPay::STATUS_WAIT_RESERVE:
                    $this->updateOrderStatus($orderId, Order::STATE_COMPLETE);
                    break;
                case LiqPay::STATUS_WAIT_SECURE:
                case LiqPay::STATUS_HOLD_WAIT:
                    $this->updateOrderStatus($orderId, Order::STATE_HOLDED);
                    break;
                case LiqPay::STATUS_WAIT_ACCEPT:
                    $this->updateOrderStatus($orderId, Order::STATE_PENDING_PAYMENT);
                    break;
                case LiqPay::STATUS_REVERSED:
                    $this->updateOrderStatus($orderId, Order::STATE_CLOSED);
                    break;
                default:
                    $this->updateOrderStatus($orderId, Order::STATE_CANCELED);
                    break;
            }
        } else {
            // Невірний підпис
            $this->_logger->error('Invalid signature for LiqPay Callback');
            die('Signature is not valid');
        }
        
        // Відповідь серверу LiqPay
        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData(['status' => 'success']);
    }
    
    protected function updateOrderStatus($orderId, $status)
    {
        try {
            $order = $this->_order->loadByIncrementId($orderId);
            if ($order->getId()) {
                $order->setState($status)->setStatus($status);
                $order->save();
                $this->_logger->info('Order status updated successfully.', ['order_id' => $orderId, 'status' => $status]);
            } else {
                $this->_logger->error('Order not found.', ['order_id' => $orderId]);
            }
        } catch (\Exception $e) {
            $this->_logger->error('Error updating order status.', ['error' => $e->getMessage()]);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

}
