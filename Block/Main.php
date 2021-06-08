<?php
namespace Monek\SecureHosting\Block;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Monek\SecureHosting\Logger\Logger;
use Magento\Framework\App\Response\Http;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;

/**
 * class Main
 *
 * Monek\SecureHosting\Block
 */
class Main extends \Magento\Framework\View\Element\Template
{
    protected $_objectmanager;
    protected $checkoutSession;
    protected $orderFactory;
    protected $urlBuilder;
    private $logger;
    protected $response;
    protected $config;
    protected $messageManager;
    protected $transactionBuilder;
    protected $inbox;

    /**
     * Construct function for block class
     *
     * @param Context                                       $context
     * @param Session                                       $checkoutSession
     * @param OrderFactory                                  $orderFactory
     * @param Logger                                        $logger
     * @param Http                                          $response
     * @param TransactionBuilder                            $tb
     * @param \Magento\AdminNotification\Model\Inbox        $inbox
     * @param \Magento\Sales\Api\OrderRepositoryInterface   $orderRepository
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        Logger $logger,
        Http $response,
        TransactionBuilder $tb,
        \Magento\AdminNotification\Model\Inbox $inbox,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {

        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->response = $response;
        $this->config = $context->getScopeConfig();
        $this->transactionBuilder = $tb;
        $this->logger = $logger;
        $this->inbox = $inbox;
        $this->orderRepository = $orderRepository;
        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                                ->get(\Magento\Framework\UrlInterface::class);
        parent::__construct($context);
        $this->setTemplate('securehosting/form.phtml');
    }

    /**
     * get order id from last order
     *
     * @return int
     */
    public function getOrderId()
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderRepository->get($orderId);

        return $order->getIncrementId();
    }
}
