<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 */

namespace Monek\SecureHosting\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * class Config
 *
 * Monek\SecureHosting\Model
 */
class Config extends \Magento\Framework\DataObject
{
    /**
     * @var string
     */
    protected $methodCode = "monek_securehosting";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *  Return config var
     *
     * @param    string Var key
     * @param    string Default value for non-existing key
     * @return   mixed
     */
    public function getConfigData($key, $default = false)
    {
        if (!$this->hasData($key)) {
            $value = $this->scopeConfig->getValue('payment/monek_securehosting/'.$key);
            if ($value === null || false === $value) {
                $value = $default;
            }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

    /**
     * get redirect message from configuration
     *
     * @return string
     */
    public function getRedirectMessage()
    {
        return $this->getConfigData('redirect_message');
    }
    
    /**
     * get SH Reference from configuration
     *
     * @return string
     */
    public function getSHreference()
    {
        return $this->getConfigData('shreference');
    }
    
    /**
     * get check code from configuration
     *
     * @return string
     */
    public function getCheckCode()
    {
        return $this->getConfigData('checkcode');
    }
    
    /**
     * get filename from configuration
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getConfigData('filename');
    }
    
    /**
     * check active status from configuration
     *
     * @return int
     */
    public function ASActive()
    {
        return (bool) $this->getConfigData('activate_as');
    }
    
    /**
     * get referrer configuration
     *
     * @return string
     */
    public function getReferrer()
    {
        return $this->getConfigData('as_referrer');
    }
    
    /**
     * get phrase configuration
     *
     * @return string
     */
    public function getPhrase()
    {
        return $this->getConfigData('as_phrase');
    }
    
    /**
     * get shared secret configuration
     *
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->getConfigData('sharedsecret');
    }
    
    /**
     * get testmode configuration
     *
     * @return int
     */
    public function testMode()
    {
        return (bool) $this->getConfigData('testmode');
    }
    
    /**
     * get redirect url from configuration test url or main url
     *
     * @return string
     */
    public function getSecureHostingRedirectUrl()
    {
        if ($this->TestMode()) {
            return "https://test.secure-server-hosting.com/secutran/secuitems.php";
        } else {
            return "https://www.secure-server-hosting.com/secutran/secuitems.php";
        }
    }
    
    /**
     * get hosting string url configuration
     *
     * @return string
     */
    public function getSecureHostingSecuStringUrl()
    {
        return "https://www.secure-server-hosting.com/secutran/create_secustring.php";
    }
    
    /**
     * get referrer url from configuration
     *
     * @return string
     */
    public function getSecureHostingCallbackReferrer()
    {
        return "https://www.secure-server-hosting.com/secutran/ProcessCallbacks.php";
    }
    
    /**
     * get auto invoice configuration
     *
     * @return int
     */
    public function autoInvoice()
    {
        return (bool) $this->getConfigData('auto_invoice');
    }
}
