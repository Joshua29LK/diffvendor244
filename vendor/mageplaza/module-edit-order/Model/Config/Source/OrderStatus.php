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
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Model\Config\Source;

/**
 * Class OrderStatus
 * @package Mageplaza\EditOrder\Model\Config\Source
 */
class OrderStatus
{
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $status = [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'processing', 'label' => 'Processing'],
            ['value' => 'complete', 'label' => 'Complete'],
            ['value' => 'canceled', 'label' => 'Canceled'],
            ['value' => 'closed', 'label' => 'Closed'],
            ['value' => 'fraud', 'label' => 'Suspected Fraud'],
            ['value' => 'holded', 'label' => 'On Hold'],
            ['value' => 'payment_review', 'label' => 'Payment Review'],
            ['value' => 'paypal_canceled_reversal', 'label' => 'PayPal Canceled Reversal'],
            ['value' => 'paypal_reversed', 'label' => 'PayPal Reversed'],
            ['value' => 'pending_paypal', 'label' => 'Pending PayPal']
        ];

        return $status;
    }
}
