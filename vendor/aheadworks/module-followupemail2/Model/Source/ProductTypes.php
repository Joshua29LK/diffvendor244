<?php
namespace Aheadworks\Followupemail2\Model\Source;

use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Convert\DataObject as ConvertDataObject;

/**
 * Class ProductTypes
 * @package Aheadworks\Followupemail2\Model\Source
 */
class ProductTypes implements OptionSourceInterface
{
    /**
     * @var ProductTypeListInterface
     */
    private $productTypeList;

    /**
     * @var ConvertDataObject
     */
    private $objectConverter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param ProductTypeListInterface $productTypeList
     * @param ConvertDataObject $objectConverter
     */
    public function __construct(
        ProductTypeListInterface $productTypeList,
        ConvertDataObject $objectConverter
    ) {
        $this->productTypeList = $productTypeList;
        $this->objectConverter = $objectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $productTypesOptions = $this->objectConverter->toOptionArray(
                $this->productTypeList->getProductTypes(),
                'name',
                'label'
            );
            array_unshift($productTypesOptions, [
                'value' => 'all',
                'label' => __('All Product Types')
            ]);
            foreach ($productTypesOptions as $key => $option) {
                if ($option['value'] == 'grouped') {
                    unset($productTypesOptions[$key]);
                }
            }
            $this->options = $productTypesOptions;
        }

        return $this->options;
    }
}
