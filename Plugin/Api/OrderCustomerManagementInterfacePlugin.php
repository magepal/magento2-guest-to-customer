<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagePal\GuestToCustomer\Plugin\Api;

class OrderCustomerManagementInterfacePlugin
{
    /**
     * @var \MagePal\GuestToCustomer\Helper\Data
     */
    protected $helperData;

    /**
     * OrderCustomerManagementInterfacePlugin constructor.
     * @param \MagePal\GuestToCustomer\Helper\Data $helperData
     */
    public function __construct(
        \MagePal\GuestToCustomer\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $subject
     * @param callable $proceed
     * @param $orderId
     * @return mixed
     */
    public function aroundCreate(
        \Magento\Sales\Api\OrderCustomerManagementInterface $subject,
        callable $proceed,
        $orderId
    ) {

        $customer = $proceed();

        if($customer && $customer->getId()){
            $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $orderId);
        }

        return $customer;
    }
}