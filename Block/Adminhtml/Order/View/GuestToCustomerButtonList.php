<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\GuestToCustomer\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ItemFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use MagePal\GuestToCustomer\Helper\Data;

/**
 * Class GuestToCustomerButtonList
 * @package MagePal\GuestToCustomer\Block\Adminhtml\Order\View
 */
class GuestToCustomerButtonList extends ButtonList
{
    /**
     * GuestToCustomerButtonList constructor.
     * @param ItemFactory $itemFactory
     * @param Registry $coreRegistry
     * @param UrlInterface $urlBuilder
     * @param Data $helperData
     */
    public function __construct(
        ItemFactory $itemFactory,
        Registry $coreRegistry,
        UrlInterface $urlBuilder,
        Data $helperData
    ) {
        parent::__construct($itemFactory);

        /** @var OrderInterface $order */
        $order = $coreRegistry->registry('current_order');

        if ($helperData->isEnabled() && $order && !$order->getCustomerId()) {
            $message ='Are you sure you want to do this?';
            $url = $urlBuilder->getUrl('guesttocustomer/customer/index');

            $this->add('guesttocustomer', [
                'label' => __('Convert to Customer'),
                'onclick' => "gustToCustomerButtonClick('{$url}', '{$order->getId()}', '{$message}')",
                'id' => 'gustToCustomerButtonClick'
            ]);
        }
    }
}
