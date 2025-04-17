<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source;

use Aheadworks\Followupemail2\Model\Source\ProductTypes;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\Convert\DataObject as ConvertDataObject;
use Magento\Catalog\Api\Data\ProductTypeInterface;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\ProductTypes
 */
class ProductTypesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductTypes
     */
    private $model;

    /**
     * @var ProductTypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTypeListMock;

    /**
     * @var ConvertDataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectConverterMock;

    private $products = [
        'simple' => 'Simple Product',
        'virtual' => 'Virtual Product',
        'configurable' => 'Configurable Product',
        'bundle' => 'Bundle Product',
        'downloadable' => 'Downloadable Product',
        'grouped' => 'Grouped Product',
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->productTypeListMock = $this->getMockBuilder(ProductTypeListInterface::class)
            ->getMockForAbstractClass();
        $this->objectConverterMock = $this->getMockBuilder(ConvertDataObject::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            ProductTypes::class,
            [
                'productTypeList' => $this->productTypeListMock,
                'objectConverter' => $this->objectConverterMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $productTypeMocks = [];
        foreach ($this->products as $code => $label) {
            $productTypeMocks[] = $this->getProductTypeMock($code, $label);
        }
        $this->productTypeListMock->expects($this->once())
            ->method('getProductTypes')
            ->willReturn($productTypeMocks);

        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($productTypeMocks)
            ->willReturn($this->getProductOptions($productTypeMocks));

        $result = [
            ['value' => 'all', 'label' => __('All Product Types')]
        ];
        foreach ($this->products as $code => $label) {
            if ($code != 'grouped') {
                $result[] = ['value' => $code, 'label' => $label];
            }
        }

        $this->assertEquals($result, $this->model->toOptionArray());
    }

    /**
     * @param string $type
     * @param string $label
     * @return ProductTypeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductTypeMock($type, $label)
    {
        $productTypeMock = $this->getMockBuilder(ProductTypeInterface::class)
            ->getMockForAbstractClass();
        $productTypeMock->expects($this->once())
            ->method('getName')
            ->willReturn($type);
        $productTypeMock->expects($this->once())
            ->method('getLabel')
            ->willReturn($label);

        return $productTypeMock;
    }

    /**
     * Get product options
     * @param ProductTypeInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $productTypeMocks
     * @return array
     */
    private function getProductOptions($productTypeMocks)
    {
        $options = [];
        foreach ($productTypeMocks as $productTypeMock) {
            $options[] = [
                'value' => $productTypeMock->getName(), 'label' => $productTypeMock->getLabel()
            ];
        }
        return $options;
    }
}
