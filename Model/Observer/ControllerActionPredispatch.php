<?php
namespace Monek\SecureHosting\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\UrlInterface;
use Monek\SecureHosting\Model\Config;
use Magento\Framework\Data\FormFactory;

/**
 * class ControllerActionPredispatch
 *
 * Monek\SecureHosting\Model\Observer
 */
# added this observer if javascript don't redirect to securehosting/redirect url.
class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var string
     */
    protected $checkoutSession;

    /**
     * @var string
     */
    protected $orderFactory;

    /**
     * @var string
     */
    protected $methodCode = "monek_securehosting";

    /**
     * @var string
     */
    protected $_config = null;

    /**
     * Construct function for observer class
     *
     * @param Session                                               $checkoutSession
     * @param OrderFactory                                          $orderFactory
     * @param UrlInterface                                          $urlInterface
     * @param Config                                                $monekConfig
     * @param FormFactory                                           $formFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
     */
    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory,
        UrlInterface $urlInterface,
        Config $monekConfig,
        FormFactory $formFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->monekConfig = $monekConfig;
        $this->_formFactory = $formFactory;
        $this->_urlInterface = $urlInterface;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * execute method for get request data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return array
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getData('request');
        if ($request->getModuleName() == "checkout" && $request->getActionName()== "success") {
            $orderId = $this->checkoutSession->getLastOrderId();
            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order->getPayment()->getMethodInstance()->getCode() == "securehosting"
                        && $order->getState() == Order::STATE_PROCESSING) {

                    $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                                            ->get(\Magento\Framework\UrlInterface::class);
                    print_r($this->_toHtml());
                    die;
                    $url = $this->monekConfig->getSecureHostingRedirectUrl();
                    header("Location:$url");
                    exit;
                }
            }
        }
    }

    /**
     * get form html
     *
     * @return array
     */
    protected function _toHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orders = $objectManager->get(\Magento\Sales\Model\Order::class)->getCollection();
        $redirect = $this->monekConfig->getSecureHostingRedirectUrl();
        $config = $objectManager->get(\Monek\SecureHosting\Model\Config::class);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setAction($config->getSecureHostingRedirectUrl())
            ->setId('SecureHosting_checkout')
            ->setName('SecureHosting_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($this->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField(
                $field,
                'hidden',
                [
                    'name' => $field,
                    'value' => $value
                ]
            );
        }
        $html = '<html><body>';
        $html.= $config->getRedirectMessage();
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("SecureHosting_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }

    /**
     * get template file data from order
     *
     * @return array
     */
    public function getStandardCheckoutFormFields()
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Cannot retrieve order object'.'%1', $e->getMessage())
            );
        }

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        if ($order->getCustomerEmail()) {
            $email = $order->getCustomerEmail();
        } elseif ($billingAddress->getEmail()) {
            $email = $billingAddress->getEmail();
        } else {
            $email = '';
        }

        $secuitems = "";
        foreach ($order->getAllVisibleItems() as $item) {
            $_p = [
                    $item->getProductId(),
                    $item->getSku(),
                    $item->getName(),
                    sprintf("%01.2f", $item->getPrice()),
                    number_format($item->getIsQtyDecimal() ?
                    (int)($item->getQtyOrdered()) : $item->getQtyOrdered(), 0, '', ''),
                    sprintf("%01.2f", $item->getRowTotal())
                ];
            $secuitems .= '['.implode('|', $_p).']';
        }
        
        $TransactionAmount = sprintf("%01.2f", $order->getBaseGrandTotal());

        $fields = [
                    'shreference'               => $this->getPaymentConfigData("shreference"),
                    'checkcode'                 => $this->getPaymentConfigData("checkcode"),
                    'filename'                  => $this->getPaymentConfigData("shreference") .
                                                    '/'. $this->getPaymentConfigData("filename"),
                    'secuitems'                 => $secuitems,
                    'orderid'                   => $order->getRealOrderId(),
                    'transactionamount'         => $TransactionAmount,
                    'subtotal'                  => sprintf("%01.2f", ($order->getBaseGrandTotal() -
                                                    ($order->getShippingAmount()+$order->getTaxAmount())), 2, ".", ""),
                    'transactiontax'            => sprintf("%01.2f", $order->getTaxAmount()),
                    'shippingcharge'            => sprintf("%01.2f", $order->getShippingAmount()),
                    'transactioncurrency'       => $order->getBaseCurrencyCode(),
                    'cardholdersname'           => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                    'cardholderaddr1'           => $billingAddress->getStreet1(),
                    'cardholderaddr2'           => $billingAddress->getStreet2(),
                    'cardholdercity'            => $billingAddress->getCity(),
                    'cardholderstate'           => $billingAddress->getRegion(),
                    'cardholderpostcode'        => $billingAddress->getPostcode(),
                    'cardholdercountry'         => $billingAddress->getCountry(),
                    'cardholdertelephonenumber' => $billingAddress->getTelephone(),
                    'shippingname'              => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                    'shippingaddr1'             => $billingAddress->getStreet1(),
                    'shippingaddr2'             => $billingAddress->getStreet2(),
                    'shippingcity'              => $billingAddress->getCity(),
                    'shippingstate'             => $billingAddress->getRegion(),
                    'shippingpostcode'          => $billingAddress->getPostcode(),
                    'shippingcountry'           => $billingAddress->getCountry(),
                    'shippingtelephonenumber'   => $billingAddress->getTelephone(),
                    'cardholdersemail'          => $email,
                    'success_url'               => $this->getSuccessURL(),
                    'callbackurl'               => $this->getNotificationURL(),
                    'callbackdata'              => "orderid|#orderid|transactionamount|#transactionamount"
        ];
        
        if ($this->getConfig()->ASActive()) {
            if (preg_match(
                '/([a-zA-Z0-9]{32})/',
                $this->getAdvancedSecuitems($secuitems, $TransactionAmount),
                $Matches
            )
            ) {
                $fields['secustring'] = $Matches[1];
            }
        }
        
        return $fields;
    }

    /**
     * get Advanced Secu items function
     *
     * @param $secuitems
     * @param $TransactionAmount
     * @return array
     */
    private function getAdvancedSecuitems($secuitems, $TransactionAmount)
    {
        $post_data = "shreference=".$this->getPaymentConfigData("shreference");
        $post_data .= "&secuitems=".$secuitems;
        $post_data .= "&secuphrase=".$this->getConfig()->getPhrase();
        $post_data .= "&transactionamount=".$TransactionAmount;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getConfig()->getSecureHostingSecuStringUrl());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $this->getConfig()->getReferrer());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $secuString = trim(curl_exec($ch));
        curl_close($ch);
        
        return $secuString;
    }

    /**
     *  Redirect URL for SecureHosting OrderPlaceRedirect
     *
     * @return      string Order Place Redirect  URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->_urlInterface->getUrl('securehosting/redirect/redirect', ['_secure' => true]);
    }
    
    /**
     *  Success URL for Order Success
     *
     * @return      string Success  URL
     */
    protected function getSuccessURL()
    {
        return $this->_urlInterface->getUrl('securehosting/redirect/success', ['_secure' => true]);
    }

    /**
     *  Return URL for SecureHosting notification
     *
     * @return      string Notification URL
     */
    protected function getNotificationURL()
    {
        return $this->_urlInterface->getUrl('securehosting/redirect/notify/', ['_secure' => true]);
    }

    /**
     * Get Config model
     *
     * @return object Monek_SecureHostingApi_Model_Config
     */
    public function getConfig()
    {
        if ($this->_config == null) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_config = $objectManager->get(\Monek\SecureHosting\Model\Config::class);
        }
        return $this->_config;
    }

    /**
     * Retrieve information from payment configuration table
     *
     * @param string $field
     *
     * @return string
     */
    public function getPaymentConfigData($field)
    {
        $code = $this->methodCode;

        $path = 'payment/' . $code . '/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
