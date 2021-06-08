<?php
namespace Monek\SecureHosting\Controller\Redirect;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

/**
 * class Success
 *
 * Monek\SecureHosting\Controller\Redirect
 */
class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var $pageFactory
     */
    protected $pageFactory;

    /**
     * Construct function for controller class
     *
     * @param Context                               $context
     * @param PageFactory                           $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * execute method for render the page data
     *
     * @return array
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
