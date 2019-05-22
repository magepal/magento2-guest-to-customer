<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 **/

namespace MagePal\GuestToCustomer\Controller\Adminhtml\Customer;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use MagePal\GuestToCustomer\Helper\Data;

/**
 * Class Index
 * @package MagePal\GuestToCustomer\Controller\Adminhtml\Customer
 */
class Index extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        Data $helperData
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->helperData = $helperData;
    }

    /**
     * Index action
     * @return Json
     * @throws Exception
     * @throws LocalizedException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id', null);
        $resultJson = $this->resultJsonFactory->create();

        if ($orderId) {
            /** @var  $order OrderInterface */
            $order = $this->orderRepository->get($orderId);

            if ($order->getEntityId()) {
                try {
                    if ($this->accountManagement->isEmailAvailable($order->getCustomerEmail())) {
                        $customer = $this->orderCustomerService->create($orderId);
                    } else {
                        $customer = $this->customerRepository->get($order->getCustomerEmail());

                        $order->setCustomerId($customer->getId());
                        $order->setCustomerIsGuest(0);
                        $this->orderRepository->save($order);
                    }

                    if ($customer && $customer->getId()) {
                        $order->setCustomerGroupId($customer->getGroupId());
                        $order->setCustomerDob($customer->getDob());
                        $order->setCustomerFirstname($customer->getFirstname());
                        $order->setCustomerLastname($customer->getLastname());
                        $order->setCustomerMiddlename($customer->getMiddlename());
                        $order->setCustomerPrefix($customer->getPrefix());
                        $order->setCustomerSuffix($customer->getSuffix());
                        $order->setCustomerTaxvat($customer->getTaxvat());
                        $order->setCustomerGender($customer->getGender());
                        $this->orderRepository->save($order);

                        $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $order->getIncrementId());
                    }

                    $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));

                    return $resultJson->setData(
                        [
                            'error' => false,
                            'message' => __('Order was successfully converted.')
                        ]
                    );
                } catch (Exception $e) {
                    return $resultJson->setData(
                        [
                            'error' => true,
                            'message' => $e->getMessage()
                        ]
                    );
                }
            } else {
                return $resultJson->setData(
                    [
                        'error' => true,
                        'message' => __('Email address already belongs to an existing customer.')
                    ]
                );
            }
        } else {
            return $resultJson->setData(
                [
                    'error' => true,
                    'message' => __('Invalid order id.')
                ]
            );
        }
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MagePal_GuestToCustomer::guesttocustomer');
    }
}
