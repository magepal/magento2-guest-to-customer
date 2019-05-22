<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */
namespace MagePal\GuestToCustomer\Controller\Guesttocustomer;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use MagePal\GuestToCustomer\Helper\Data;

/**
 * Class LookupformPost
 * @package MagePal\GuestToCustomer\Controller\Guesttocustomer
 */
class LookupformPost extends AbstractAccount
{
    /**
     * @var Data
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
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        Data $helperData,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
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
     * @return Redirect|Page
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey
            && $this->getRequest()->isPost()
            && $incrementId = $this->getRequest()->getPost('order_increment')
        ) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'increment_id',
                $incrementId,
                'eq'
            )->create();

            $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();

            if ($order->getId()) {
                $customer = $this->session->getCustomer();

                if (!$order->getCustomerId() && $order->getCustomerEmail() === $customer->getEmail()) {
                    $this->helperData->setCustomerData($order, $customer);

                    $comment = sprintf(
                        __("Guest order converted by customer: %s"),
                        $customer->getEmail()
                    );
                    $order->addStatusHistoryComment($comment);

                    $this->orderRepository->save($order);

                    $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $incrementId);

                    $this->messageManager->addSuccessMessage(__('Order was successfully added to your account'));
                } else {
                    $this->messageManager->addErrorMessage(__('Order was not placed by you.'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('We can\'t find the order.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Unknown error please try again.'));
        }

        return $resultRedirect->setPath('*/*/lookupform');
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->helperData->isEnabledCustomerDashbard() || !$this->session->isLoggedIn()) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');

            return $resultRedirect;
        }

        return parent::dispatch($request);
    }
}
