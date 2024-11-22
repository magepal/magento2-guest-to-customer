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
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var Session
     */
    private $authSession;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param Session $authSession
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        Session $authSession,
        Data $helperData,
        StoreManagerInterface $storeManager,
        Emulation $emulation
    ) {
        parent::__construct($context);

        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerRepository = $customerRepository;
        $this->authSession = $authSession;
        $this->helperData = $helperData;
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
    }

    /**
     * Index action
     * @return Json
     */
    public function execute()
    {
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id', null);
        $resultJson = $this->resultJsonFactory->create();

        /** @var  $order OrderInterface */
        $order = $this->orderRepository->get($orderId);

        if ($orderId && $order->getEntityId()) {
            try {
                //get website id from order

                if ($this->isEmailAvailable($order->getCustomerEmail(), $order->getStore()->getWebsiteId())) {
                    $this->emulation->startEnvironmentEmulation($order->getStoreId(), 'adminhtml');
                    $customer = $this->orderCustomerService->create($orderId);
                    $this->emulation->stopEnvironmentEmulation();
                } elseif ($this->helperData->isMergeIfCustomerAlreadyExists()) {
                    $customer = $this->customerRepository->get($order->getCustomerEmail());
                } else {
                    return $resultJson->setData(
                        $this->getMessage(true, 'Customer with email address already exists')
                    );
                }

                $this->helperData->setCustomerData($order, $customer);

                $comment = sprintf(
                    __("Guest order converted by admin user: %s"),
                    $this->authSession->getUser()->getUserName()
                );

                $order->addStatusHistoryComment($comment);

                $this->orderRepository->save($order);

                $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $order->getIncrementId());

                $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));

                return $resultJson->setData($this->getMessage(false, 'Order was successfully converted.'));
            } catch (Exception $e) {
                return $resultJson->setData($this->getMessage(true, $e->getMessage()));
            }
        } else {
            return $resultJson->setData($this->getMessage(true, 'Invalid order id.'));
        }
    }

    /**
     * @inheritdoc
     *
     * @param string $customerEmail
     * @param int|null $websiteId
     * @return bool
     * @throws LocalizedException
     */
    public function isEmailAvailable($customerEmail, $websiteId = null)
    {
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->customerRepository->get($customerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
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

    /**
     * @param $hasError
     * @param $message
     * @return array
     */
    protected function getMessage($hasError, $message)
    {
        return [
            'error' => $hasError,
            'message' => __($message)
        ];
    }
}
