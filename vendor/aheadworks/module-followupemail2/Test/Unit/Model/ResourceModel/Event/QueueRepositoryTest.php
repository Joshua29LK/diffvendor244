<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\QueueRepository;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Collection as EventQueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\CollectionFactory as EventQueueCollectionFactory;
use Aheadworks\Followupemail2\Model\Event\Queue as EventQueueModel;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Processor as ScheduledEmailsIndexer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SortOrder;

/**
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\Event\QueueRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueueRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueueRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var EventQueueInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueFactoryMock;

    /**
     * @var EventQueueSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueSearchResultsFactoryMock;

    /**
     * @var EventQueueCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueCollectionFactoryMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var ScheduledEmailsIndexer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledEmailsIndexerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['load', 'delete', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueFactoryMock = $this->getMockBuilder(EventQueueInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueSearchResultsFactoryMock = $this->getMockBuilder(
            EventQueueSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventQueueCollectionFactoryMock = $this->getMockBuilder(EventQueueCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduledEmailsIndexerMock = $this->getMockBuilder(ScheduledEmailsIndexer::class)
            ->setMethods(['reindexRow'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            QueueRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'eventQueueFactory' => $this->eventQueueFactoryMock,
                'eventQueueSearchResultsFactory' => $this->eventQueueSearchResultsFactoryMock,
                'eventQueueCollectionFactory' => $this->eventQueueCollectionFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'scheduledEmailsIndexer' => $this->scheduledEmailsIndexerMock,
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $eventQueueId = 1;
        $eventQueueMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventQueueId);

        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($eventQueueMock)
            ->willReturn($eventQueueMock);

        $this->scheduledEmailsIndexerMock->expects($this->once())
            ->method('reindexRow')
            ->with($eventQueueId)
            ->willReturn(null);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventQueueMock, $eventQueueId)
            ->willReturn($eventQueueMock);

        $this->assertSame($eventQueueMock, $this->model->save($eventQueueMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $eventQueueId = 1;
        $eventQueueMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventQueueId);

        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventQueueMock, $eventQueueId)
            ->willReturn($eventQueueMock);

        $this->assertSame($eventQueueMock, $this->model->get($eventQueueId));
    }

    /**
     * Test get method if specified event queue item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $eventQueueId = 1;
        $eventQueueMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventQueueMock, $eventQueueId)
            ->willReturn($eventQueueMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($eventQueueId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'event_type';
        $filterValue = EventInterface::TYPE_ABANDONED_CART;
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(EventQueueSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->eventQueueSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(EventQueueCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventQueueCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $eventQueueModelMock = $this->getMockBuilder(EventQueueModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventQueueModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'event_type' => $filterValue
            ]);

        $filterGroupMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn(false);
        $filterMock->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn($filterName);
        $filterMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($filterValue);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with([$filterName], [['eq' => $filterValue]]);
        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getCurrentPage')
            ->willReturn(1);
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getPageSize')
            ->willReturn(1);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterName);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($filterName, SortOrder::SORT_ASC);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$eventQueueModelMock]));

        $eventQueueMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$eventQueueMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $eventQueueId = 1;
        $eventQueueMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventQueueId);

        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventQueueMock, $eventQueueId)
            ->willReturn($eventQueueMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($eventQueueMock)
            ->willReturn($eventQueueMock);

        $this->assertTrue($this->model->delete($eventQueueMock));
    }

    /**
     * Test delete method if specified event queue item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $eventQueueId = 1;
        $eventQueueOneMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventQueueId);

        $eventQueueTwoMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventQueueOneMock, $eventQueueId)
            ->willReturn($eventQueueTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($eventQueueOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $eventQueueId = 1;
        $eventQueueMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventQueueId);

        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventQueueMock, $eventQueueId)
            ->willReturn($eventQueueMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($eventQueueMock)
            ->willReturn($eventQueueMock);

        $this->assertTrue($this->model->deleteById($eventQueueId));
    }

    /**
     * Test deleteById method if specified event queue item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $eventQueueId = 1;
        $eventQueueMock = $this->getMockForAbstractClass(EventQueueInterface::class);
        $eventQueueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventQueueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventQueueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventQueueMock, $eventQueueId)
            ->willReturn($eventQueueMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($eventQueueId);
    }
}
