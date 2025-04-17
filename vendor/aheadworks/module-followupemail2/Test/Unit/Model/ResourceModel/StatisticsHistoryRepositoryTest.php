<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel;

use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\HistoryRepository as StatisticsHistoryRepository;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistorySearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistorySearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\Collection as StatisticsHistoryCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\CollectionFactory
    as StatisticsHistoryCollectionFactory;
use Aheadworks\Followupemail2\Model\StatisticsHistory as StatisticsHistoryModel;
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
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\StatisticsHistoryRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatisticsHistoryRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StatisticsHistoryRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var StatisticsHistoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsHistoryFactoryMock;

    /**
     * @var StatisticsHistorySearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsHistorySearchResultsFactoryMock;

    /**
     * @var StatisticsHistoryCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsHistoryCollectionFactoryMock;

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

        $this->statisticsHistoryFactoryMock = $this->getMockBuilder(StatisticsHistoryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->statisticsHistorySearchResultsFactoryMock = $this->getMockBuilder(
            StatisticsHistorySearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->statisticsHistoryCollectionFactoryMock = $this->getMockBuilder(StatisticsHistoryCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            StatisticsHistoryRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'statisticsHistoryFactory' => $this->statisticsHistoryFactoryMock,
                'statisticsHistorySearchResultsFactory' => $this->statisticsHistorySearchResultsFactoryMock,
                'statisticsHistoryCollectionFactory' => $this->statisticsHistoryCollectionFactoryMock,
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
        $historyId = 1;
        $historyMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($historyId);

        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($historyMock)
            ->willReturn($historyMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($historyMock, $historyId)
            ->willReturn($historyMock);

        $this->assertSame($historyMock, $this->model->save($historyMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $historyId = 1;
        $historyMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($historyId);

        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($historyMock, $historyId)
            ->willReturn($historyMock);

        $this->assertSame($historyMock, $this->model->get($historyId));
    }

    /**
     * Test get method if specified history does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $historyId = 1;
        $historyMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($historyMock, $historyId)
            ->willReturn($historyMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($historyId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'email';
        $filterValue = 'test@example.com';
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(
            StatisticsHistorySearchResultsInterface::class,
            [],
            '',
            false
        );
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->statisticsHistorySearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(StatisticsHistoryCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statisticsHistoryCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $historyModelMock = $this->getMockBuilder(StatisticsHistoryModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $historyModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'email' => $filterValue
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
            ->willReturn(new \ArrayIterator([$historyModelMock]));

        $historyMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$historyMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $historyId = 1;
        $historyMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($historyId);

        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($historyMock, $historyId)
            ->willReturn($historyMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($historyMock)
            ->willReturn($historyMock);

        $this->assertTrue($this->model->delete($historyMock));
    }

    /**
     * Test delete method if specified history does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $historyId = 1;
        $historyOneMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($historyId);

        $historyTwoMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($historyOneMock, $historyId)
            ->willReturn($historyTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($historyOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $historyId = 1;
        $historyMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($historyId);

        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($historyMock, $historyId)
            ->willReturn($historyMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($historyMock)
            ->willReturn($historyMock);

        $this->assertTrue($this->model->deleteById($historyId));
    }

    /**
     * Test deleteById method if specified history does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $historyId = 1;
        $historyMock = $this->getMockForAbstractClass(StatisticsHistoryInterface::class);
        $historyMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->statisticsHistoryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($historyMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($historyMock, $historyId)
            ->willReturn($historyMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($historyId);
    }
}
