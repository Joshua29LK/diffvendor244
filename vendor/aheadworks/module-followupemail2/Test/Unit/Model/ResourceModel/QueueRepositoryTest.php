<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel;

use Aheadworks\Followupemail2\Model\ResourceModel\QueueRepository;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\QueueSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\QueueSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\Collection as QueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Aheadworks\Followupemail2\Model\Queue as QueueModel;
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
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\QueueRepository
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
     * @var QueueInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueFactoryMock;

    /**
     * @var QueueSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueSearchResultsFactoryMock;

    /**
     * @var QueueCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queueCollectionFactoryMock;

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

        $this->queueFactoryMock = $this->getMockBuilder(QueueInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueSearchResultsFactoryMock = $this->getMockBuilder(
            QueueSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->queueCollectionFactoryMock = $this->getMockBuilder(QueueCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            QueueRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'queueFactory' => $this->queueFactoryMock,
                'queueSearchResultsFactory' => $this->queueSearchResultsFactoryMock,
                'queueCollectionFactory' => $this->queueCollectionFactoryMock,
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
        $queueId = 1;
        $queueMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($queueId);

        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($queueMock)
            ->willReturn($queueMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($queueMock, $queueId)
            ->willReturn($queueMock);

        $this->assertSame($queueMock, $this->model->save($queueMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $queueId = 1;
        $queueMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($queueId);

        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($queueMock, $queueId)
            ->willReturn($queueMock);

        $this->assertSame($queueMock, $this->model->get($queueId));
    }

    /**
     * Test get method if specified queue item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $queueId = 1;
        $queueMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($queueMock, $queueId)
            ->willReturn($queueMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($queueId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'recipient_email';
        $filterValue = 'test@example.com';
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(QueueSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->queueSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(QueueCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $queueModelMock = $this->getMockBuilder(QueueModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $queueModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'recipient_email' => 'test@example.com'
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
            ->willReturn(new \ArrayIterator([$queueModelMock]));

        $queueMock = $this->getMockForAbstractClass(QueueInterface::class);
        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$queueMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $queueId = 1;
        $queueMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($queueId);

        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($queueMock, $queueId)
            ->willReturn($queueMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($queueMock)
            ->willReturn($queueMock);

        $this->assertTrue($this->model->delete($queueMock));
    }

    /**
     * Test delete method if specified queue item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $queueId = 1;
        $queueOneMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($queueId);

        $queueTwoMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($queueOneMock, $queueId)
            ->willReturn($queueTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($queueOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $queueId = 1;
        $queueMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($queueId);

        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($queueMock, $queueId)
            ->willReturn($queueMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($queueMock)
            ->willReturn($queueMock);

        $this->assertTrue($this->model->deleteById($queueId));
    }

    /**
     * Test deleteById method if specified queue item does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $queueId = 1;
        $queueMock = $this->getMockForAbstractClass(QueueInterface::class);
        $queueMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->queueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($queueMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($queueMock, $queueId)
            ->willReturn($queueMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($queueId);
    }
}
