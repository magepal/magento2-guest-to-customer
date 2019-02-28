<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 **/

namespace MagePal\GuestToCustomer\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Index extends Action
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \MagePal\GuestToCustomer\Helper\Data
     */
    protected $helperData;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \MagePal\GuestToCustomer\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \MagePal\GuestToCustomer\Helper\Data $helperData
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->helperData = $helperData;
    }

    /**
     * Index action
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id', null);
        $resultJson = $this->resultJsonFactory->create();

        if ($orderId) {
            /** @var  $order \Magento\Sales\Api\Data\OrderInterface */
            $order = $this->orderRepository->get($orderId);

            if ($order->getEntityId() && $this->accountManagement->isEmailAvailable($order->getEmailAddress())) {
                try {
                    $customer = $this->orderCustomerService->create($orderId);

                    if ($customer && $customer->getId()) {
                        $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $order->getIncrementId());
                    }

                    $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));

                    return $resultJson->setData(
                        [
                            'error' => false,
                            'message' => __('Order was successfully converted.')
                        ]
                    );
                } catch (\Exception $e) {
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
                        'message' => __('Email address already belong to an existing customer.')
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
