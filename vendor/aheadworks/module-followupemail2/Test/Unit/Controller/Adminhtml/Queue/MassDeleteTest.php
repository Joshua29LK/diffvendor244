<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Controller\Adminhtml\Queue\MassDelete;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\Collection as QueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Queue\MassDelete
 */
class MassDeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MassDelete
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var QueueCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueCollectionFactoryMock;

    /**
     * @var QueueRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueCollectionFactoryMock = $this->getMockBuilder(QueueCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueRepositoryMock = $this->getMockBuilder(QueueRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            MassDelete::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'queueCollectionFactory' => $this->queueCollectionFactoryMock,
                'queueRepository' => $this->queueRepositoryMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $queueIds = [1, 2];
        $queueSize = 2;

        $collectionMock = $this->getMockBuilder(QueueCollection::class)
            ->setMethods(['getSize', 'getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($queueSize);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($queueIds);
        $this->queueCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->queueRepositoryMock->expects($this->atLeastOnce())
            ->method('deleteById')
            ->willReturn(true);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 email(s) have been deleted.', $queueSize))
            ->willReturnSelf();

        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when an exception occurs
     */
    public function testExecuteWithException()
    {
        $queueIds = [1, 2];
        $queueSize = 2;

        $collectionMock = $this->getMockBuilder(QueueCollection::class)
            ->setMethods(['getSize', 'getAllIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($queueSize);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($queueIds);
        $this->queueCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);

        $this->queueRepositoryMock->expects($this->atLeastOnce())
            ->method('deleteById')
            ->willThrowException(new NoSuchEntityException());

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('No such entity.'))
            ->willReturnSelf();

        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }
}
