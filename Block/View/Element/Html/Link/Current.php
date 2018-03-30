<?php
/**
 * Copyright © MagePal LLC, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */
namespace MagePal\GuestToCustomer\Block\View\Element\Html\Link;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{

    /* @var \MagePal\GuestToCustomer\Helper\Data*/
    protected $helperData;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MagePal\GuestToCustomer\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        array $data = [],
        \MagePal\GuestToCustomer\Helper\Data $helperData
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helperData = $helperData;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helperData->isEnabledCustomerDashbard()) {
            return parent::_toHtml();
        }

        return '';
    }
}
