<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

namespace MagePal\GuestToCustomer\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class UpdateDownloadableProductObserver implements ObserverInterface
{
    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $purchasedFactory;

    /**
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     */
    public function __construct(
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
    ) {
        $this->purchasedFactory = $purchasedFactory;
    }

    /**
     * @param EventObserver $observer
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        $incrementId = $observer->getEvent()->getIncrementId();
        $customerId = $observer->getEvent()->getCustomerId();

        try {
            if ($incrementId && $customerId) {
                $purchased = $this->purchasedFactory->create()->load(
                    $incrementId,
                    'order_increment_id'
                );

                if ($purchased->getId()) {
                    $purchased->setCustomerId($customerId);
                    $purchased->save();
                }
            }
        } catch (\Exception $e) {
            //do nothing
        }
    }
}