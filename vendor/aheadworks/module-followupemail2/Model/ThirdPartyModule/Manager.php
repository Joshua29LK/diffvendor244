<?php
namespace Aheadworks\Followupemail2\Model\ThirdPartyModule;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Manager
 *
 * @package Aheadworks\Followupemail2\Model\ThirdPartyModule
 */
class Manager
{
    /**
     * AW Customer Segmentation module name
     */
    const AW_CUSTOMER_SEGMENTATION_MODULE_NAME = 'Aheadworks_CustomerSegmentation';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Check if AW Customer Segmentation module is enabled
     *
     * @return bool
     */
    public function isAwCustomerSegmentationModuleEnabled()
    {
        return $this->moduleList->has(self::AW_CUSTOMER_SEGMENTATION_MODULE_NAME);
    }
}
