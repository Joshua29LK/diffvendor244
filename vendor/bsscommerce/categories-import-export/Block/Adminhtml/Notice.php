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
namespace Bss\CategoriesImportExport\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

/**
 * Class Notice
 * @package Bss\CategoriesImportExport\Block\Adminhtml
 */
class Notice extends Template
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Notice constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return bool
     */
    public function isCoreModuleDisabled()
    {
        if ($this->moduleManager->isEnabled("Bss_ImportExportCore")) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getCoreModuleLink()
    {
        return "https://bsscommerce.com/";
    }
}
