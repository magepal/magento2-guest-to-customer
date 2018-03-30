<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
*/
namespace MagePal\GuestToCustomer\Controller\Guesttocustomer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\View\Result\PageFactory;

class LookupformPost extends \Magento\Customer\Controller\AbstractAccount
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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \MagePal\GuestToCustomer\Helper\Data $helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        Validator $formKeyValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * Customer login form page
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost() && $incrementId = $this->getRequest()->getPost('order_increment')) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId, 'eq')->create();
            $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

            if (
                $order->getId() && !$order->getCustomerId()
                && $order->getCustomerEmail() === $this->session->getCustomer()->getEmail()
            ) {
                $order->setCustomerId($this->session->getCustomerId());
                $order->setCustomerIsGuest(0);
                $this->orderRepository->save($order);

                $this->helperData->dispatchCustomerOrderLinkEvent($this->session->getCustomerId(), $incrementId);

                $this->messageManager->addSuccessMessage(__('Order was successfully added to your account'));
            } else {
                $this->messageManager->addErrorMessage(__('Unknown error please try again.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find the order.'));
        }

        return $resultRedirect->setPath('*/*/lookupform');
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
