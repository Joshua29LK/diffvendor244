<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel;

use Aheadworks\Followupemail2\Model\ResourceModel\CampaignRepository;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\Collection as CampaignCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;
use Aheadworks\Followupemail2\Model\Campaign as CampaignModel;
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
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\CampaignRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CampaignRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CampaignRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var CampaignInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignFactoryMock;

    /**
     * @var CampaignSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignSearchResultsFactoryMock;

    /**
     * @var CampaignCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignCollectionFactoryMock;

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

        $this->campaignFactoryMock = $this->getMockBuilder(CampaignInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->campaignSearchResultsFactoryMock = $this->getMockBuilder(
            CampaignSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->campaignCollectionFactoryMock = $this->getMockBuilder(CampaignCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            CampaignRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'campaignFactory' => $this->campaignFactoryMock,
                'campaignSearchResultsFactory' => $this->campaignSearchResultsFactoryMock,
                'campaignCollectionFactory' => $this->campaignCollectionFactoryMock,
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
        $campaignId = 1;
        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($campaignId);

        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($campaignMock)
            ->willReturn($campaignMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($campaignMock, $campaignId)
            ->willReturn($campaignMock);

        $this->assertSame($campaignMock, $this->model->save($campaignMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $campaignId = 1;
        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($campaignId);

        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($campaignMock, $campaignId)
            ->willReturn($campaignMock);

        $this->assertSame($campaignMock, $this->model->get($campaignId));
    }

    /**
     * Test get method if specified campaign does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $campaignId = 1;
        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($campaignMock, $campaignId)
            ->willReturn($campaignMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($campaignId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'Name';
        $filterValue = 'Campaign';
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(CampaignSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->campaignSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(CampaignCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->campaignCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $campaignModelMock = $this->getMockBuilder(CampaignModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $campaignModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'name' => 'Test campaign'
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
            ->willReturn(new \ArrayIterator([$campaignModelMock]));

        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$campaignMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $campaignId = 1;
        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($campaignId);

        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($campaignMock, $campaignId)
            ->willReturn($campaignMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($campaignMock)
            ->willReturn($campaignMock);

        $this->assertTrue($this->model->delete($campaignMock));
    }

    /**
     * Test delete method if specified campaign does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $campaignId = 1;
        $campaignOneMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($campaignId);

        $campaignTwoMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($campaignOneMock, $campaignId)
            ->willReturn($campaignTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($campaignOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $campaignId = 1;
        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($campaignId);

        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($campaignMock, $campaignId)
            ->willReturn($campaignMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($campaignMock)
            ->willReturn($campaignMock);

        $this->assertTrue($this->model->deleteById($campaignId));
    }

    /**
     * Test deleteById method if specified campaign does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $campaignId = 1;
        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $campaignMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($campaignMock, $campaignId)
            ->willReturn($campaignMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($campaignId);
    }
}
