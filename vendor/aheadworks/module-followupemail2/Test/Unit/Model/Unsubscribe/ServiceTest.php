<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Unsubscribe;

use Aheadworks\Followupemail2\Model\Unsubscribe\Service;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeSearchResultsInterface;
use Aheadworks\Followupemail2\Api\UnsubscribeRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Unsubscribe\Service
 */
class ServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Service
     */
    private $model;

    /**
     * @var UnsubscribeInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsubscribeFactoryMock;

    /**
     * @var UnsubscribeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unsubscribeRepositoryMock;

    /**
     * @var EventRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->unsubscribeFactoryMock = $this->getMockBuilder(UnsubscribeInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->unsubscribeRepositoryMock = $this->getMockBuilder(UnsubscribeRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventRepositoryMock = $this->getMockBuilder(EventRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Service::class,
            [
                'unsubscribeFactory' => $this->unsubscribeFactoryMock,
                'unsubscribeRepository' => $this->unsubscribeRepositoryMock,
                'eventRepository' => $this->eventRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Test unsubscribeFromEvent method
     */
    public function testUnsubscribeFromEvent()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 0;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_ID, 'eq'],
                [UnsubscribeInterface::VALUE, $eventId, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('setType')
            ->with(UnsubscribeInterface::TYPE_EVENT_ID)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setValue')
            ->with($eventId)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('save')
            ->with($unsubscribeItemMock)
            ->willReturn($unsubscribeItemMock);

        $this->assertTrue($this->model->unsubscribeFromEvent($eventId, $email, $storeId));
    }

    /**
     * Test unsubscribeFromEvent method if email already unsubscribed
     */
    public function testUnsubscribeFromEventAlreadyUnsubscribed()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_ID, 'eq'],
                [UnsubscribeInterface::VALUE, $eventId, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $this->assertTrue($this->model->unsubscribeFromEvent($eventId, $email, $storeId));
    }

    /**
     * Test unsubscribeFromEvent method if error occurs
     */
    public function testUnsubscribeFromEventErrorUnsubscribe()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 0;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_ID, 'eq'],
                [UnsubscribeInterface::VALUE, $eventId, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('setType')
            ->with(UnsubscribeInterface::TYPE_EVENT_ID)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setValue')
            ->with($eventId)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('save')
            ->with($unsubscribeItemMock)
            ->willThrowException(new \Exception('Unknown error!'));

        $this->assertFalse($this->model->unsubscribeFromEvent($eventId, $email, $storeId));
    }

    /**
     * Test unsubscribeFromEventType method
     */
    public function testUnsubscribeFromEventType()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 0;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_TYPE, 'eq'],
                [UnsubscribeInterface::VALUE, $eventId, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('setType')
            ->with(UnsubscribeInterface::TYPE_EVENT_TYPE)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setValue')
            ->with($eventId)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('save')
            ->with($unsubscribeItemMock)
            ->willReturn($unsubscribeItemMock);

        $this->assertTrue($this->model->unsubscribeFromEventType($eventId, $email, $storeId));
    }

    /**
     * Test unsubscribeFromEventType method if email already unsubscribed
     */
    public function testUnsubscribeFromEventTypeAlreadyUnsubscribed()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_TYPE, 'eq'],
                [UnsubscribeInterface::VALUE, $eventId, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $this->assertTrue($this->model->unsubscribeFromEventType($eventId, $email, $storeId));
    }

    /**
     * Test unsubscribeFromEventType method if error occurs
     */
    public function testUnsubscribeFromEventTypeErrorUnsubscribe()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 0;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_TYPE, 'eq'],
                [UnsubscribeInterface::VALUE, $eventId, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('setType')
            ->with(UnsubscribeInterface::TYPE_EVENT_TYPE)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setValue')
            ->with($eventId)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('save')
            ->with($unsubscribeItemMock)
            ->willThrowException(new \Exception('Unknown error!'));

        $this->assertFalse($this->model->unsubscribeFromEventType($eventId, $email, $storeId));
    }

    /**
     * Test unsubscribeFromAll method
     */
    public function testUnsubscribeFromAll()
    {
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 0;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_ALL, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('setType')
            ->with(UnsubscribeInterface::TYPE_ALL)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setValue')
            ->with(null)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('save')
            ->with($unsubscribeItemMock)
            ->willReturn($unsubscribeItemMock);

        $this->assertTrue($this->model->unsubscribeFromAll($email, $storeId));
    }

    /**
     * Test unsubscribeFromAll method if email already unsubscribed
     */
    public function testUnsubscribeFromAllAlreadyUnsubscribed()
    {
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_ALL, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $this->assertTrue($this->model->unsubscribeFromAll($email, $storeId));
    }

    /**
     * Test unsubscribeFromAll method if error occurs
     */
    public function testUnsubscribeFromAllErrorUnsubscribe()
    {
        $email = 'test@example.com';
        $storeId = 1;
        $unsubscribeItemsCount = 0;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_ALL, 'eq'],
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($unsubscribeItemsCount);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('setType')
            ->with(UnsubscribeInterface::TYPE_ALL)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setValue')
            ->with(null)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $unsubscribeItemMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $this->unsubscribeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($unsubscribeItemMock);

        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('save')
            ->with($unsubscribeItemMock)
            ->willThrowException(new \Exception('Unknown error!'));

        $this->assertFalse($this->model->unsubscribeFromAll($email, $storeId));
    }

    /**
     * Test isUnsubscribed method if email is unsubscribed from all
     */
    public function testIsUnsubscribedFromAll()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('getType')
            ->willReturn(UnsubscribeInterface::TYPE_ALL);
        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$unsubscribeItemMock]);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $this->assertTrue($this->model->isUnsubscribed($eventId, $email, $storeId));
    }

    /**
     * Test isUnsubscribed method if email is unsubscribed from event type
     */
    public function testIsUnsubscribedFromEventType()
    {
        $eventId = 10;
        $eventType = 'abandoned_cart';
        $email = 'test@example.com';
        $storeId = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('getType')
            ->willReturn(UnsubscribeInterface::TYPE_EVENT_TYPE);
        $unsubscribeItemMock->expects($this->once())
            ->method('getValue')
            ->willReturn($eventType);
        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$unsubscribeItemMock]);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getEventType')
            ->willReturn($eventType);
        $this->eventRepositoryMock->expects($this->once())
            ->method('get')
            ->with($eventId)
            ->willReturn($eventMock);

        $this->assertTrue($this->model->isUnsubscribed($eventId, $email, $storeId));
    }

    /**
     * Test isUnsubscribed method if email is unsubscribed from event
     */
    public function testIsUnsubscribedFromEvent()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeItemMock = $this->getMockBuilder(UnsubscribeInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeItemMock->expects($this->once())
            ->method('getType')
            ->willReturn(UnsubscribeInterface::TYPE_EVENT_ID);
        $unsubscribeItemMock->expects($this->once())
            ->method('getValue')
            ->willReturn($eventId);
        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$unsubscribeItemMock]);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $this->assertTrue($this->model->isUnsubscribed($eventId, $email, $storeId));
    }

    /**
     * Test isUnsubscribed method if email is not unsubscribed
     */
    public function testIsUnsubscribedIfNotUnsubscribed()
    {
        $eventId = 10;
        $email = 'test@example.com';
        $storeId = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [UnsubscribeInterface::EMAIL, $email, 'eq'],
                [UnsubscribeInterface::STORE_ID, $storeId, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $unsubscribeSearchResultsMock = $this->getMockBuilder(UnsubscribeSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $unsubscribeSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->unsubscribeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($unsubscribeSearchResultsMock);

        $this->assertFalse($this->model->isUnsubscribed($eventId, $email, $storeId));
    }
}
