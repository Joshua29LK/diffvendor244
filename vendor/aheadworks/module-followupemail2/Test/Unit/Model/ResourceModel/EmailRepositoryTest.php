<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\ResourceModel;

use Aheadworks\Followupemail2\Model\ResourceModel\EmailRepository;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterfaceFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\Collection as EmailCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Email\CollectionFactory as EmailCollectionFactory;
use Aheadworks\Followupemail2\Model\Email as EmailModel;
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
 * Test for \Aheadworks\Followupemail2\Model\ResourceModel\EmailRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmailRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var EmailInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailFactoryMock;

    /**
     * @var EmailSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailSearchResultsFactoryMock;

    /**
     * @var EmailCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailCollectionFactoryMock;

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

        $this->emailFactoryMock = $this->getMockBuilder(EmailInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailSearchResultsFactoryMock = $this->getMockBuilder(
            EmailSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailCollectionFactoryMock = $this->getMockBuilder(EmailCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            EmailRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'emailFactory' => $this->emailFactoryMock,
                'emailSearchResultsFactory' => $this->emailSearchResultsFactoryMock,
                'emailCollectionFactory' => $this->emailCollectionFactoryMock,
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
        $emailId = 1;
        $emailMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($emailId);

        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($emailMock, $emailId)
            ->willReturn($emailMock);

        $this->assertSame($emailMock, $this->model->save($emailMock));
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $emailId = 1;
        $emailMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($emailId);

        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($emailMock, $emailId)
            ->willReturn($emailMock);

        $this->assertSame($emailMock, $this->model->get($emailId));
    }

    /**
     * Test get method if specified email does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $emailId = 1;
        $emailMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($emailMock, $emailId)
            ->willReturn($emailMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->get($emailId);
    }

    /**
     * Test getList method
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'Name';
        $filterValue = 'Email';
        $collectionSize = 5;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(EmailSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->emailSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(EmailCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $emailModelMock = $this->getMockBuilder(EmailModel::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $emailModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'name' => 'Test email'
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
            ->willReturn(new \ArrayIterator([$emailModelMock]));

        $emailMock = $this->getMockForAbstractClass(EmailInterface::class);
        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$emailMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $emailId = 1;
        $emailMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($emailId);

        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($emailMock, $emailId)
            ->willReturn($emailMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->assertTrue($this->model->delete($emailMock));
    }

    /**
     * Test delete method if specified email does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteException()
    {
        $emailId = 1;
        $emailOneMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($emailId);

        $emailTwoMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($emailOneMock, $emailId)
            ->willReturn($emailTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->delete($emailOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $emailId = 1;
        $emailMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($emailId);

        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($emailMock, $emailId)
            ->willReturn($emailMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->assertTrue($this->model->deleteById($emailId));
    }

    /**
     * Test deleteById method if specified email does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDeleteByIdException()
    {
        $emailId = 1;
        $emailMock = $this->getMockForAbstractClass(EmailInterface::class);
        $emailMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->emailFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($emailMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($emailMock, $emailId)
            ->willReturn($emailMock);
        $this->expectException(NoSuchEntityException::class);
        $this->model->deleteById($emailId);
    }
}
