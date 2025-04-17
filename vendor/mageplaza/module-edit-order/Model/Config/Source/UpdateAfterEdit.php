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
class UpdateAfterEdit
{
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arrayValues = [
            ['value' => '0', 'label' => 'No Change In Value'],
            ['value' => '1', 'label' => 'Create new offline invoice and cancel old Invoices/Shipments/Credit Memos']
        ];

        return $arrayValues;
    }
}
