<?php

declare(strict_types=1);

namespace Amasty\ShopbyBrand\Ui\Component\Listing\Columns;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class BrandAttribute extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const ORIG_ATTRIBUTE_CODE = 'orig_attribute_code';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AttributeRepositoryInterface $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach (($dataSource['data']['items'] ?? []) as $key => $item) {
            $attributeCode = substr($item[$this->getData('name')], 5);
            $dataSource['data']['items'][$key][self::ORIG_ATTRIBUTE_CODE] = $attributeCode;
            try {
                $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
                $viewLink = $this->urlBuilder->getUrl(
                    'catalog/product_attribute/edit',
                    ['attribute_id' => $attribute->getAttributeId()]
                );

                $dataSource['data']['items'][$key][$this->getData('name')] = sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    $viewLink,
                    $attributeCode
                );
            } catch (\Exception $ex) {
                $dataSource['data']['items'][$key][$this->getData('name')] = $attributeCode;
            }
        }

        return $dataSource;
    }
}
