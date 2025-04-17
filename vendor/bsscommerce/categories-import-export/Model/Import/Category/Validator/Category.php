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
namespace Bss\CategoriesImportExport\Model\Import\Category\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Bss\CategoriesImportExport\Model\Import\Category\RowValidatorInterface;

/**
 * Class Category
 *
 * @package Bss\CategoriesImportExport\Model\Import\Category\Validator
 */
class Category extends AbstractValidator implements RowValidatorInterface
{
    /**
     * Is valid
     *
     * @param mixed $value
     * @return bool|void
     * @throws \Zend_Validate_Exception
     */
    public function isValid($value)
    {
        AbstractValidator::isValid($value);
    }

    /**
     * Check empty category name
     *
     * @param string $categoryName
     * @param int $storeId
     * @return bool
     */
    public function checkEmptyCategoryName($categoryName, $storeId)
    {
        if ($categoryName == "" && $storeId == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $parentId
     * @return bool
     */
    public function checkEmptyParentId($parentId)
    {
        if ($parentId == "" || !$parentId) {
            return false;
        }
        return true;
    }

    /**
     * Validate date
     *
     * @param string $date
     * @return bool
     */
    public function validateCustomDesignDate($date)
    {
        if ($date != "") {
            $patternDate = "~([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))|"
                . "([12]\d{3}/(0[1-9]|1[0-2])/(0[1-9]|[12]\d|3[01]))~";
            if (!preg_match($patternDate, $date)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check empty storeId
     *
     * @param string $storeId
     * @return bool
     */
    public function isEmptyStoreId($storeId)
    {
        return $storeId == "";
    }
}
