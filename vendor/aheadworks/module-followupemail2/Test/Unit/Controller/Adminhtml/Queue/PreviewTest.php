<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Queue\Preview;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\ViewInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue\Preview
 */
class PreviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Preview
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var QueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueRepositoryMock;

    /**
     * @var QueueManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManagementMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->viewMock = $this->getMockBuilder(ViewInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock
            ]
        );

        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->setMethods(['register'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueRepositoryMock = $this->getMockBuilder(QueueRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->queueManagementMock = $this->getMockBuilder(QueueManagementInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            Preview::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->registryMock,
                'queueRepository' => $this->queueRepositoryMock,
                'queueManagement' => $this->queueManagementMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $queueId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($queueId);

        $queueItemMock = $this->getMockBuilder(QueueInterface::class)
            ->getMockForAbstractClass();
        $this->queueRepositoryMock->expects($this->once())
            ->method('get')
            ->with($queueId)
            ->willReturn($queueItemMock);

        $previewMock = $this->getMockBuilder(PreviewInterface::class)
            ->getMockForAbstractClass();
        $this->queueManagementMock->expects($this->once())
            ->method('getPreview')
            ->with($queueItemMock)
            ->willReturn($previewMock);

        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('aw_followupemail2_preview', $previewMock)
            ->willReturnSelf();

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->willReturnSelf();
        $this->viewMock->expects($this->once())
            ->method('renderLayout')
            ->willReturnSelf();

        $this->controller->execute();
    }
}
