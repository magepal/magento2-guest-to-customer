<?php


namespace MagePal\GuestToCustomer\Block\Adminhtml\Order\View;

class GuestToCustomerButtonList extends \Magento\Backend\Block\Widget\Button\ButtonList
{
    /**
     * GuestToCustomerButtonList constructor.
     * @param \Magento\Backend\Block\Widget\Button\ItemFactory $itemFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \MagePal\GuestToCustomer\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Button\ItemFactory $itemFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MagePal\GuestToCustomer\Helper\Data $helperData
    ) {
        parent::__construct($itemFactory);

        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
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
