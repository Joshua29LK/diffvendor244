<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel;

use Aheadworks\Followupemail2\Model\ResourceModel\EventHistoryRepository;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EventHistorySearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistorySearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\EventHistory\Collection as EventHistoryCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\EventHistory\CollectionFactory as EventHistoryCollectionFactory;
use Aheadworks\Followupemail2\Model\EventHistory as EventHistoryModel;
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
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\EventHistoryRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventHistoryRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventHistoryRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var EventHistoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryFactoryMock;

    /**
     * @var EventHistorySearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistorySearchResultsFactoryMock;

    /**
     * @var EventHistoryCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryCollectionFactoryMock;

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

        $this->eventHistoryFactoryMock = $this->getMockBuilder(EventHistoryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventHistorySearchResultsFactoryMock = $this->getMockBuilder(
            EventHistorySearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventHistoryCollectionFactoryMock = $this->getMockBuilder(EventHistoryCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            EventHistoryRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'eventHistoryFactory' => $this->eventHistoryFactoryMock,
                'eventHistorySearchResultsFactory' => $this->eventHistorySearchResultsFactoryMock,
                'eventHistoryCollectionFactory' => $this->eventHistoryCollectionFactoryMock,
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
        $eventHistoryId = 1;
        $eventHistoryMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventHistoryId);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($eventHistoryMock)
            ->willReturn($eventHistoryMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventHistoryMock, $eventHistoryId)
            ->willReturn($eventHistoryMock);

        $this->assertSame($eventHistoryMock, $this->model->save($eventHistoryMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $eventHistoryId = 1;
        $eventHistoryMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventHistoryId);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventHistoryMock, $eventHistoryId)
            ->willReturn($eventHistoryMock);

        $this->assertSame($eventHistoryMock, $this->model->get($eventHistoryId));
    }

    /**
     * Test get method if specified event history does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $eventHistoryId = 1;
        $eventHistoryMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventHistoryMock, $eventHistoryId)
            ->willReturn($eventHistoryMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($eventHistoryId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'event_type';
        $filterValue = 'abandoned_checkout';
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(EventHistorySearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->eventHistorySearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(EventHistoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventHistoryCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $eventHistoryModelMock = $this->getMockBuilder(EventHistoryModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventHistoryModelMock->expects($this->once())
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
            ->willReturn(new \ArrayIterator([$eventHistoryModelMock]));

        $eventHistoryMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$eventHistoryMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $eventHistoryId = 1;
        $eventHistoryMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventHistoryId);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventHistoryMock, $eventHistoryId)
            ->willReturn($eventHistoryMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($eventHistoryMock)
            ->willReturn($eventHistoryMock);

        $this->assertTrue($this->model->delete($eventHistoryMock));
    }

    /**
     * Test delete method if specified event history does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $eventHistoryId = 1;
        $eventHistoryOneMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventHistoryId);

        $eventHistoryTwoMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventHistoryOneMock, $eventHistoryId)
            ->willReturn($eventHistoryTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($eventHistoryOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $eventHistoryId = 1;
        $eventHistoryMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($eventHistoryId);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventHistoryMock, $eventHistoryId)
            ->willReturn($eventHistoryMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($eventHistoryMock)
            ->willReturn($eventHistoryMock);

        $this->assertTrue($this->model->deleteById($eventHistoryId));
    }

    /**
     * Test deleteById method if specified event history does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $eventHistoryId = 1;
        $eventHistoryMock = $this->getMockForAbstractClass(EventHistoryInterface::class);
        $eventHistoryMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->eventHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($eventHistoryMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($eventHistoryMock, $eventHistoryId)
            ->willReturn($eventHistoryMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($eventHistoryId);
    }
}
