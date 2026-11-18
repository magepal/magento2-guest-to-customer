<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\GuestToCustomer\Observer;

use Exception;
use Magento\Downloadable\Model\Link\PurchasedFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased as PurchasedResource;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class UpdateDownloadableProductObserver
 * @package MagePal\GuestToCustomer\Observer
 */
class UpdateDownloadableProductObserver implements ObserverInterface
{
    /**
     * @var PurchasedFactory
     */
    protected $purchasedFactory;

    /**
     * @var PurchasedResource
     */
    protected $purchasedResource;

    /**
     * @param PurchasedFactory $purchasedFactory
     * @param PurchasedResource $purchasedResource
     */
    public function __construct(
        PurchasedFactory $purchasedFactory,
        PurchasedResource $purchasedResource
    ) {
        $this->purchasedFactory = $purchasedFactory;
        $this->purchasedResource = $purchasedResource;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        $incrementId = $observer->getEvent()->getIncrementId();
        $customerId = $observer->getEvent()->getCustomerId();

        try {
            if ($incrementId && $customerId) {
                $purchased = $this->purchasedFactory->create();
                $this->purchasedResource->load(
                    $purchased,
                    $incrementId,
                    'order_increment_id'
                );

                if ($purchased->getId()) {
                    $purchased->setCustomerId($customerId);
                    $this->purchasedResource->save($purchased);
                }
            }
        } catch (Exception $e) {
            //do nothing
        }
    }
}
