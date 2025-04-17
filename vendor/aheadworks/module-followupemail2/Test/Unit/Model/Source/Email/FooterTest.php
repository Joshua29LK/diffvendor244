<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Email;

use Aheadworks\Followupemail2\Model\Source\Email\Footer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Email\Model\Template\Config as TemplateConfig;
use Magento\Email\Model\ResourceModel\Template\Collection as TemplateCollection;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Email\Footer
 */
class FooterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Footer
     */
    private $model;

    /**
     * @var TemplateConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateConfigMock;

    /**
     * @var TemplateCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateCollectionFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->templateConfigMock = $this->getMockBuilder(TemplateConfig::class)
            ->setMethods(['getTemplateLabel'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateCollectionFactoryMock = $this->getMockBuilder(TemplateCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Footer::class,
            [
                'templateConfig' => $this->templateConfigMock,
                'templateCollectionFactory' => $this->templateCollectionFactoryMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $templateLabel = 'Footer';
        $resultOptions = [
            ['value' => '-1', 'label' => __('No template')],
            ['value' => 'design_email_footer_template', 'label' => __('%1 (Default)', $templateLabel)]
        ];

        $collectionMock = $this->getMockBuilder(TemplateCollection::class)
            ->setMethods(['load', 'toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([]);
        $this->templateCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->templateConfigMock->expects($this->once())
            ->method('getTemplateLabel')
            ->willReturn($templateLabel);

        $this->assertEquals($resultOptions, $this->model->toOptionArray());
    }
}
