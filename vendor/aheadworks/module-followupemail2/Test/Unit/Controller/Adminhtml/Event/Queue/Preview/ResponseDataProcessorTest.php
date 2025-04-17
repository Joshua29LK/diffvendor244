<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Queue\Preview;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Preview\ResponseDataProcessor;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Block\Adminhtml\Preview as PreviewBlock;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;

/**
 * Test for \Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Preview\ResponseDataProcessor
 */
class ResponseDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResponseDataProcessor
     */
    private $model;

    /**
     * @var EventQueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueManagementMock;

    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->eventQueueManagementMock = $this->getMockBuilder(EventQueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            ResponseDataProcessor::class,
            [
                'eventQueueManagement' => $this->eventQueueManagementMock,
                'layoutFactory' => $this->layoutFactoryMock,
            ]
        );
    }

    /**
     * Test getPreparedData method
     */
    public function testGetPreparedData()
    {
        $eventQueueItemId = 1;
        $renderedBlock = '<p>Test preview</p>';
        $result = [
            'id' => $eventQueueItemId,
            'preview' => $renderedBlock,
        ];

        $eventQueueItemMock = $this->getMockBuilder(EventQueueInterface::class)
            ->getMockForAbstractClass();
        $eventQueueItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventQueueItemId);

        $previewMock = $this->getMockBuilder(PreviewInterface::class)
            ->getMockForAbstractClass();
        $this->eventQueueManagementMock->expects($this->once())
            ->method('getScheduledEmailPreview')
            ->with($eventQueueItemMock)
            ->willReturn($previewMock);

        $previewBlockMock = $this->getMockBuilder(PreviewBlock::class)
            ->setMethods(['setPreview', 'toHtml'])
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(PreviewBlock::class)
            ->willReturn($previewBlockMock);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);

        $previewBlockMock->expects($this->once())
            ->method('setPreview')
            ->with($previewMock)
            ->willReturnSelf();
        $previewBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($renderedBlock);

        $this->assertEquals($result, $this->model->getPreparedData($eventQueueItemMock));
    }
}
