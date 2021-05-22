<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */
namespace MagePal\GuestToCustomer\Controller\Guesttocustomer;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use MagePal\GuestToCustomer\Helper\Data;

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
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->helperData->isEnabledCustomerDashboard() || !$this->session->isLoggedIn()) {
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        $incrementId = $this->getRequest()->getPost('order_increment');

        if ($validFormKey && $this->getRequest()->isPost() && $incrementId) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'increment_id',
                $incrementId,
                'eq'
            )->create();

            $orderList = $this->orderRepository->getList($searchCriteria);

            if ($orderList->getTotalCount()) {
                $order = current($orderList->getItems());

                try {
                    $customer = $this->session->getCustomerData();
                    $this->addCustomerIdToOrder($customer, $order);
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__('Unknown error please try again.'));
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
     * @param CustomerInterface|false $customer
     * @param  Order | OrderInterface $order
     */
    protected function addCustomerIdToOrder($customer, $order)
    {
        if ($customer && !$order->getCustomerId()
            && strcasecmp($order->getCustomerEmail(), $customer->getEmail()) == 0
        ) {
            $this->helperData->setCustomerData($order, $customer);

            $comment = sprintf(
                __("Guest order converted by customer: %s"),
                $customer->getEmail()
            );

            $order->addStatusHistoryComment($comment);
            $this->orderRepository->save($order);

            $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $order->getIncrementId());

            $this->messageManager->addSuccessMessage(__('Order was successfully added to your account'));
        } else {
            $this->messageManager->addErrorMessage(__('We are unable to find your order number, please try again.'));
        }
    }
}
