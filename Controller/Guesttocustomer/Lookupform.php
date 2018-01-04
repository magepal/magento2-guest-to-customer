<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagePal\GuestToCustomer\Controller\Guesttocustomer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

class Lookupform extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \MagePal\GuestToCustomer\Helper\Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \MagePal\GuestToCustomer\Helper\Data $helperData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->helperData = $helperData;

        parent::__construct($context);
    }

    /**
     * Customer login form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        // $title = __('Guest to customer');

        $resultPage->getConfig()->getTitle()->set(__(''));
        $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);

        return $resultPage;
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->helperData->isEnabledCustomerDashbard() || !$this->session->isLoggedIn()) {
            $this->_redirect('customer/account/login');
        }

        return parent::dispatch($request);
    }
}
