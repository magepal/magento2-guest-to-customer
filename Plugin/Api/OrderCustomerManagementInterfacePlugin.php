<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
*/

namespace MagePal\GuestToCustomer\Plugin\Api;

use Magento\Sales\Api\OrderCustomerManagementInterface;
use MagePal\GuestToCustomer\Helper\Data;

class OrderCustomerManagementInterfacePlugin
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * OrderCustomerManagementInterfacePlugin constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param OrderCustomerManagementInterface $subject
     * @param callable $proceed
     * @param $orderId
     * @return mixed
     */
    public function aroundCreate(
        OrderCustomerManagementInterface $subject,
        callable $proceed,
        $orderId
    ) {
        $customer = $proceed($orderId);

        if ($customer && $customer->getId()) {
            $this->helperData->dispatchCustomerOrderLinkEvent($customer->getId(), $orderId);
        }

        return $customer;
    }
}
