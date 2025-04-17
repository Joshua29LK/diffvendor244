<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel;

use Aheadworks\Followupemail2\Model\ResourceModel\UnsubscribeRepository;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe\Collection as UnsubscribeCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe\CollectionFactory as UnsubscribeCollectionFactory;
use Aheadworks\Followupemail2\Model\Unsubscribe as UnsubscribeModel;
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
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\UnsubscribeRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnsubscribeRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UnsubscribeRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var UnsubscribeInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsubscribeFactoryMock;

    /**
     * @var UnsubscribeSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsubscribeSearchResultsFactoryMock;

    /**
     * @var UnsubscribeCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsubscribeCollectionFactoryMock;

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

        $this->unsubscribeFactoryMock = $this->getMockBuilder(UnsubscribeInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->unsubscribeSearchResultsFactoryMock = $this->getMockBuilder(
            UnsubscribeSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->unsubscribeCollectionFactoryMock = $this->getMockBuilder(UnsubscribeCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            UnsubscribeRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'unsubscribeFactory' => $this->unsubscribeFactoryMock,
                'unsubscribeSearchResultsFactory' => $this->unsubscribeSearchResultsFactoryMock,
                'unsubscribeCollectionFactory' => $this->unsubscribeCollectionFactoryMock,
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
        $unsubscribeItemId = 1;
        $unsubscribeItemMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($unsubscribeItemId);

        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($unsubscribeItemMock)
            ->willReturn($unsubscribeItemMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($unsubscribeItemMock, $unsubscribeItemId)
            ->willReturn($unsubscribeItemMock);

        $this->assertSame($unsubscribeItemMock, $this->model->save($unsubscribeItemMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $unsubscribeItemId = 1;
        $unsubscribeItemMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($unsubscribeItemId);

        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($unsubscribeItemMock, $unsubscribeItemId)
            ->willReturn($unsubscribeItemMock);

        $this->assertSame($unsubscribeItemMock, $this->model->get($unsubscribeItemId));
    }

    /**
     * Test get method if specified unsubscribe item item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $unsubscribeItemId = 1;
        $unsubscribeItemMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($unsubscribeItemMock, $unsubscribeItemId)
            ->willReturn($unsubscribeItemMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($unsubscribeItemId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'email';
        $filterValue = 'test@test.tt';
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(UnsubscribeSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->unsubscribeSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(UnsubscribeCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->unsubscribeCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $unsubscribeModelMock = $this->getMockBuilder(UnsubscribeModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $unsubscribeModelMock->expects($this->once())
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
            ->willReturn(new \ArrayIterator([$unsubscribeModelMock]));

        $unsubscribeItemMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$unsubscribeItemMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $unsubscribeItemId = 1;
        $unsubscribeItemMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($unsubscribeItemId);

        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($unsubscribeItemMock, $unsubscribeItemId)
            ->willReturn($unsubscribeItemMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($unsubscribeItemMock)
            ->willReturn($unsubscribeItemMock);

        $this->assertTrue($this->model->delete($unsubscribeItemMock));
    }

    /**
     * Test delete method if specified unsubscribe item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $unsubscribeItemId = 1;
        $unsubscribeItemOneMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($unsubscribeItemId);

        $unsubscribeItemTwoMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($unsubscribeItemOneMock, $unsubscribeItemId)
            ->willReturn($unsubscribeItemTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($unsubscribeItemOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $unsubscribeItemId = 1;
        $unsubscribeItemMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($unsubscribeItemId);

        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($unsubscribeItemMock, $unsubscribeItemId)
            ->willReturn($unsubscribeItemMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($unsubscribeItemMock)
            ->willReturn($unsubscribeItemMock);

        $this->assertTrue($this->model->deleteById($unsubscribeItemId));
    }

    /**
     * Test deleteById method if specified unsubscribe item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $unsubscribeItemId = 1;
        $unsubscribeItemMock = $this->getMockForAbstractClass(UnsubscribeInterface::class);
        $unsubscribeItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($unsubscribeItemMock, $unsubscribeItemId)
            ->willReturn($unsubscribeItemMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($unsubscribeItemId);
    }
}
