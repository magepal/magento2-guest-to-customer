<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
*/

namespace MagePal\GuestToCustomer\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ACTIVE = 'guesttocustomer/general/active';
    const XML_CUSTOMER_DASHBOARD = 'guesttocustomer/general/customer_dashboard';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Whether is active
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Customer dashboard active
     *
     * @return bool
     */
    public function isEnabledCustomerDashbard()
    {
        return $this->isEnabled() && $this->scopeConfig->getValue(self::XML_CUSTOMER_DASHBOARD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $customerId int
     * @param $orderId int
     * @return $this
     */
    public function dispatchCustomerOrderLinkEvent($customerId, $orderId)
    {
        $this->_eventManager->dispatch('magepal_guest_to_customer_save', [
            'customer_id' => $customerId,
            'order_id' => $orderId //$incrementId
        ]);

        return $this;
    }
}
