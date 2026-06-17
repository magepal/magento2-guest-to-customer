<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\GuestToCustomer\Observer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use MagePal\GuestToCustomer\Helper\Data;

/**
 * Class SalesOrderPlaceAfterObserver
 * @package MagePal\GuestToCustomer\Observer
 */
class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Data $helperData
    ) {
        $this->customerRepository = $customerRepository;
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->helperData->isMergeIfCustomerAlreadyExists()) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        if ($order->getCustomerId()) {
            return;
        }

        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail(), $order->getStore()->getWebsiteId());
            if (!$customer->getId()) {
                return;
            }
            $order->setCustomerId($customer->getId());
            $order->save();
        } catch (Exception $e) {
            //do nothing
        }
    }
}
