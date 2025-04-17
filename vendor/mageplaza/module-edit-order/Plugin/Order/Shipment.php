<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_EditOrder
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Plugin\Order;

use Closure;
use Magento\Sales\Model\Order\Item as ShipmentItem;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class Shipment
 * @package Mageplaza\EditOrder\Plugin\Shipment
 */
class Shipment
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param ShipmentItem $object
     * @param Closure $process
     * @return mixed
     */
    public function aroundGetSimpleQtyToShip(ShipmentItem $object, Closure $process)
    {
        $order = $object->getOrder();
        if ($this->helperData->isEnabled() && (int) $order->getMpIsEditOrder() === 1) {
            $qty = 0;
            if (($object->getQtyOrdered() > $object->getQtyRefunded()) && ($object->getQtyOrdered() > $object->getQtyShipped())) {
                if ($object->getQtyOrdered() > ($object->getQtyShipped() + $object->getQtyRefunded() + $object->getQtyCanceled())) {
                    if ($object->getQtyRefunded() === $object->getQtyShipped()) {
                        $qty = $object->getQtyOrdered() - $object->getQtyShipped() - $object->getQtyCanceled();
                    } else {
                        $qty = $object->getQtyOrdered() - $object->getQtyShipped() - $object->getQtyRefunded() - $object->getQtyCanceled();
                    }
                }
            }

            return max(round($qty, 8), 0);
        }

        return $process();
    }
}
