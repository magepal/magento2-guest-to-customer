<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\GuestToCustomer\Helper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package MagePal\GuestToCustomer\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_ACTIVE = 'guesttocustomer/general/active';
    const XML_CUSTOMER_DASHBOARD = 'guesttocustomer/general/customer_dashboard';
    const XML_CUSTOMER_ALREADY_EXISTS = 'guesttocustomer/general/merge_customer_already_exists';

    const XML_MERGE_CUSTOMER_GROUP = 'guesttocustomer/merge/group';
    const XML_MERGE_CUSTOMER_NAME = 'guesttocustomer/merge/name';
    const XML_MERGE_CUSTOMER_DOB = 'guesttocustomer/merge/dob';
    const XML_MERGE_CUSTOMER_GENDER = 'guesttocustomer/merge/gender';
    const XML_MERGE_CUSTOMER_TAXVAT = 'guesttocustomer/merge/taxvat';

    /**
     * @param Context $context
     * @param ObjectManagerInterface
     */
    public function __construct(
        Context $context
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
        return $this->isConfigEnabled(self::XML_PATH_ACTIVE);
    }

    /**
     * Customer dashboard active
     *
     * @return bool
     */
    public function isEnabledCustomerDashboard()
    {
        return $this->isEnabled() && $this->isConfigEnabled(self::XML_CUSTOMER_DASHBOARD);
    }

    /**
     * Automatically add order to existing customer with same email address.
     * @return bool
     */
    public function isMergeIfCustomerAlreadyExists()
    {
        return $this->isConfigEnabled(self::XML_CUSTOMER_ALREADY_EXISTS);
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
            'order_id' => $orderId, //incrementId
            'increment_id' => $orderId //$incrementId
        ]);

        return $this;
    }

    /**
     * @param $order OrderInterface
     * @param $customer CustomerInterface
     */
    public function setCustomerData(OrderInterface $order, CustomerInterface $customer)
    {
        $order->setCustomerIsGuest(0);
        $order->setCustomerId($customer->getId());

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_GROUP)) {
            $order->setCustomerGroupId($customer->getGroupId());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_NAME)) {
            $order->setCustomerPrefix($customer->getPrefix());

            $order->setCustomerFirstname($customer->getFirstname());
            $order->setCustomerLastname($customer->getLastname());
            $order->setCustomerMiddlename($customer->getMiddlename());

            $order->setCustomerSuffix($customer->getSuffix());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_DOB)) {
            $order->setCustomerDob($customer->getDob());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_GENDER)) {
            $order->setCustomerGender($customer->getGender());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_TAXVAT)) {
            $order->setCustomerTaxvat($customer->getTaxvat());
        }
    }

    /**
     * @param $xmlPath string
     *
     * @return boolean
     */
    protected function isConfigEnabled($xmlPath)
    {
        return $this->scopeConfig->isSetFlag(
            $xmlPath,
            ScopeInterface::SCOPE_STORE
        );
    }
}
