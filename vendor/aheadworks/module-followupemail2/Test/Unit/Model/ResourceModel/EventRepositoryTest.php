<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel;

use Aheadworks\Followupemail2\Model\ResourceModel\EventRepository;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Collection as EventCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Aheadworks\Followupemail2\Model\Event as EventModel;
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
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\EventRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var EventInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventFactoryMock;

    /**
     * @var EventSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventSearchResultsFactoryMock;

    /**
     * @var EventCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventCollectionFactoryMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

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

        $this->eventFactoryMock = $this->getMockBuilder(EventInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventSearchResultsFactoryMock = $this->getMockBuilder(
            EventSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventCollectionFactoryMock = $this->getMockBuilder(EventCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            EventRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'eventFactory' => $this->eventFactoryMock,
                'eventSearchResultsFactory' => $this->eventSearchResultsFactoryMock,
                'eventCollectionFactory' => $this->eventCollectionFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $eventId = 1;
        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($eventMock)
            ->willReturn($eventMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventMock, $eventId)
            ->willReturn($eventMock);

        $this->assertSame($eventMock, $this->model->save($eventMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $eventId = 1;
        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventMock, $eventId)
            ->willReturn($eventMock);

        $this->assertSame($eventMock, $this->model->get($eventId));
    }

    /**
     * Test get method if specified event does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $eventId = 1;
        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventMock, $eventId)
            ->willReturn($eventMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($eventId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'Name';
        $filterValue = 'Event';
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(EventSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->eventSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(EventCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $eventModelMock = $this->getMockBuilder(EventModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'name' => 'Test event'
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
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$eventModelMock]));

        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$eventMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $eventId = 1;
        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventMock, $eventId)
            ->willReturn($eventMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($eventMock)
            ->willReturn($eventMock);

        $this->assertTrue($this->model->delete($eventMock));
    }

    /**
     * Test delete method if specified event does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $eventId = 1;
        $eventOneMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $eventTwoMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventOneMock, $eventId)
            ->willReturn($eventTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($eventOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $eventId = 1;
        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventId);

        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventMock, $eventId)
            ->willReturn($eventMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($eventMock)
            ->willReturn($eventMock);

        $this->assertTrue($this->model->deleteById($eventId));
    }

    /**
     * Test deleteById method if specified event does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $eventId = 1;
        $eventMock = $this->getMockForAbstractClass(EventInterface::class);
        $eventMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventMock, $eventId)
            ->willReturn($eventMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($eventId);
    }
}
