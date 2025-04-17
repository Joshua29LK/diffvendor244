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
namespace Bss\CategoriesImportExport\Model\Import\Category;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_INVALID_PATH = 'errorInvalidPath';

    const ERROR_INVALID_PARENT_ID = 'errorInvalidParentId';

    const ERROR_EXIST_URL_KEY = 'errorExistUrlKey';

    const ERROR_EMPTY_CATEGORY_NAME = 'errorEmptyCategoryName';

    const ERROR_CATEGORY_ID_NOT_EXIST = 'errorCategoryIdNotExist';

    const ERROR_WRONG_DATE_FORMAT = 'errorWrongDateFormat';
}
