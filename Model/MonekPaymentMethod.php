<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Monek\SecureHosting\Model;

class MonekPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var string
     */
    protected $methodCode = "monek_securehosting";
    
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $redirect_uri;
    
    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'securehosting';
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function getOrderPlaceRedirectUrl()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
                            ->get(\Magento\Framework\UrlInterface::class)
                            ->getUrl("securehosting/redirect");
    }
}
