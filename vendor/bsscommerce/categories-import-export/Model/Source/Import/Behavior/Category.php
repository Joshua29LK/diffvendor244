<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CategoriesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CategoriesImportExport\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;

class Category extends \Magento\ImportExport\Model\Source\Import\Behavior\Basic
{
    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return 'bss_category';
    }

    /**
     * @return array
     */
    public function getEnableBehaviorFields()
    {
        $fields = [
            "behavior" => [],
            Import::FIELD_NAME_VALIDATION_STRATEGY => [],
            Import::FIELD_NAME_ALLOWED_ERROR_COUNT => [],
            Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => [
                "value" => "|"
            ]
        ];
        return $fields;
    }
}
