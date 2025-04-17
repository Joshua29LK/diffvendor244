<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model;

use Aheadworks\Followupemail2\Model\Statistics;
use Aheadworks\Followupemail2\Model\StatisticsManagement;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\StatisticsHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\Updater as StatisticsUpdater;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\History\Updater as StatisticsHistoryUpdater;
use Aheadworks\Followupemail2\Model\Statistics as StatisticsModel;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\Collection as StatisticsCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Statistics\CollectionFactory as StatisticsCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\StatisticsManagement
 */
class StatisticsManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StatisticsManagement
     */
    private $model;

    /**
     * @var EventManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagementMock;

    /**
     * @var StatisticsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsInterfaceFactoryMock;

    /**
     * @var StatisticsCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsCollectionFactoryMock;

    /**
     * @var StatisticsHistoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsHistoryInterfaceFactoryMock;

    /**
     * @var StatisticsHistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsHistoryRepositoryMock;

    /**
     * @var StatisticsUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsUpdaterMock;

    /**
     * @var StatisticsHistoryUpdater|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsHistoryUpdaterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->eventManagementMock = $this->getMockBuilder(EventManagementInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsInterfaceFactoryMock = $this->getMockBuilder(StatisticsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->statisticsCollectionFactoryMock = $this->getMockBuilder(StatisticsCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->statisticsHistoryInterfaceFactoryMock = $this->getMockBuilder(StatisticsHistoryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->statisticsHistoryRepositoryMock = $this->getMockBuilder(StatisticsHistoryRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsUpdaterMock = $this->getMockBuilder(StatisticsUpdater::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->statisticsHistoryUpdaterMock = $this->getMockBuilder(StatisticsHistoryUpdater::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            StatisticsManagement::class,
            [
                'eventManagement' => $this->eventManagementMock,
                'statisticsInterfaceFactory' => $this->statisticsInterfaceFactoryMock,
                'statisticsCollectionFactory' => $this->statisticsCollectionFactoryMock,
                'statisticsHistoryInterfaceFactory' => $this->statisticsHistoryInterfaceFactoryMock,
                'statisticsHistoryRepository' => $this->statisticsHistoryRepositoryMock,
                'statisticsUpdater' => $this->statisticsUpdaterMock,
                'statisticsHistoryUpdater' => $this->statisticsHistoryUpdaterMock,
            ]
        );
    }

    /**
     * Test addNew method
     */
    public function testAddNew()
    {
        $email = 'test@example.com';
        $emailContentId = 100;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setEmailContentId')
            ->with($emailContentId)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setSent')
            ->with(0)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setOpened')
            ->with(0)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setClicked')
            ->with(0)
            ->willReturnSelf();
        $this->statisticsHistoryInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statHistoryMock);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($statHistoryMock)
            ->willReturn($statHistoryMock);

        $this->assertEquals($statHistoryMock, $this->model->addNew($email, $emailContentId));
    }

    /**
     * Test addNew method if an error occurs
     */
    public function testAddNewException()
    {
        $email = 'test@example.com';
        $emailContentId = 100;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('setEmail')
            ->with($email)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setEmailContentId')
            ->with($emailContentId)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setSent')
            ->with(0)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setOpened')
            ->with(0)
            ->willReturnSelf();
        $statHistoryMock->expects($this->once())
            ->method('setClicked')
            ->with(0)
            ->willReturnSelf();
        $this->statisticsHistoryInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statHistoryMock);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($statHistoryMock)
            ->willThrowException(new NoSuchEntityException());

        $this->assertNull($this->model->addNew($email, $emailContentId));
    }

    /**
     * Test addSent method
     */
    public function testAddSent()
    {
        $statId = 100;
        $email = 'test@example.com';
        $sent = 0;
        $emailContentId = 10;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getSent')
            ->willReturn($sent);
        $statHistoryMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);
        $statHistoryMock->expects($this->once())
            ->method('getEmailContentId')
            ->willReturn($emailContentId);
        $statHistoryMock->expects($this->once())
            ->method('setSent')
            ->with(1)
            ->willReturnSelf();

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);
        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($statHistoryMock)
            ->willReturn($statHistoryMock);

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(true);

        $this->assertTrue($this->model->addSent($statId, $email));
    }

    /**
     * Test addSent method if sent already set
     */
    public function testAddSentIfSentAlreadySet()
    {
        $statId = 100;
        $email = 'test@example.com';
        $sent = 1;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getSent')
            ->willReturn($sent);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);

        $this->assertTrue($this->model->addSent($statId, $email));
    }

    /**
     * Test addSent method if email not the same
     */
    public function testAddSentIfEmailNotTheSame()
    {
        $statId = 100;
        $email1 = 'test1@example.com';
        $email2 = 'test2@example.com';
        $sent = 0;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getSent')
            ->willReturn($sent);
        $statHistoryMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email1);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);

        $this->assertTrue($this->model->addSent($statId, $email2));
    }

    /**
     * Test addSent method if no statistics with such id
     */
    public function testAddSentException()
    {
        $statId = 100;
        $email = 'test@example.com';

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willThrowException(new NoSuchEntityException());

        $this->assertFalse($this->model->addSent($statId, $email));
    }

    /**
     * Test addOpened method
     */
    public function testAddOpened()
    {
        $statId = 100;
        $email = 'test@example.com';
        $opened = 0;
        $emailContentId = 10;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getOpened')
            ->willReturn($opened);
        $statHistoryMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);
        $statHistoryMock->expects($this->once())
            ->method('getEmailContentId')
            ->willReturn($emailContentId);
        $statHistoryMock->expects($this->once())
            ->method('setOpened')
            ->with(1)
            ->willReturnSelf();

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);
        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($statHistoryMock)
            ->willReturn($statHistoryMock);

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(true);

        $this->assertTrue($this->model->addOpened($statId, $email));
    }

    /**
     * Test addOpened method if opened already set
     */
    public function testAddOpenedIfSentAlreadySet()
    {
        $statId = 100;
        $email = 'test@example.com';
        $opened = 1;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getOpened')
            ->willReturn($opened);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);

        $this->assertTrue($this->model->addOpened($statId, $email));
    }

    /**
     * Test addOpened method if email not the same
     */
    public function testAddOpenedIfEmailNotTheSame()
    {
        $statId = 100;
        $email1 = 'test1@example.com';
        $email2 = 'test2@example.com';
        $opened = 0;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getOpened')
            ->willReturn($opened);
        $statHistoryMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email1);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);

        $this->assertTrue($this->model->addOpened($statId, $email2));
    }

    /**
     * Test addOpened method if no statistics with such id
     */
    public function testAddOpenedException()
    {
        $statId = 100;
        $email = 'test@example.com';

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willThrowException(new NoSuchEntityException());

        $this->assertFalse($this->model->addOpened($statId, $email));
    }

    /**
     * Test addClicked method
     */
    public function testAddClicked()
    {
        $statId = 100;
        $email = 'test@example.com';
        $clicked = 0;
        $emailContentId = 10;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getClicked')
            ->willReturn($clicked);
        $statHistoryMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);
        $statHistoryMock->expects($this->once())
            ->method('getEmailContentId')
            ->willReturn($emailContentId);
        $statHistoryMock->expects($this->once())
            ->method('setClicked')
            ->with(1)
            ->willReturnSelf();

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);
        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('save')
            ->with($statHistoryMock)
            ->willReturn($statHistoryMock);

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(true);

        $this->assertTrue($this->model->addClicked($statId, $email));
    }

    /**
     * Test addClicked method if opened already set
     */
    public function testAddClickedIfSentAlreadySet()
    {
        $statId = 100;
        $email = 'test@example.com';
        $clicked = 1;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getClicked')
            ->willReturn($clicked);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);

        $this->assertTrue($this->model->addClicked($statId, $email));
    }

    /**
     * Test addClicked method if email not the same
     */
    public function testAddClickedIfEmailNotTheSame()
    {
        $statId = 100;
        $email1 = 'test1@example.com';
        $email2 = 'test2@example.com';
        $clicked = 0;

        $statHistoryMock = $this->getMockBuilder(StatisticsHistoryInterface::class)
            ->getMockForAbstractClass();
        $statHistoryMock->expects($this->once())
            ->method('getClicked')
            ->willReturn($clicked);
        $statHistoryMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($email1);

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willReturn($statHistoryMock);

        $this->assertTrue($this->model->addClicked($statId, $email2));
    }

    /**
     * Test addClicked method if no statistics with such id
     */
    public function testAddClickedException()
    {
        $statId = 100;
        $email = 'test@example.com';

        $this->statisticsHistoryRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statId)
            ->willThrowException(new NoSuchEntityException());

        $this->assertFalse($this->model->addClicked($statId, $email));
    }

    /**
     * Test getByCampaignId method
     */
    public function testGetByCampaignId()
    {
        $campaignId = 1;
        $eventId = 10;

        $eventMock = $this->getMockBuilder(EventInterface::class)
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())
            ->method('getId')
            ->willReturn($eventId);

        $this->eventManagementMock->expects($this->once())
            ->method('getEventsByCampaignId')
            ->with($campaignId)
            ->willReturn([$eventMock]);

        $collectionItemMock = $this->getMockBuilder(StatisticsModel::class)
            ->setMethods(['getSent', 'getOpened', 'getClicked'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItemMock->expects($this->once())
            ->method('getSent')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getOpened')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getClicked')
            ->willReturn(0);
        $collectionMock = $this->getMockBuilder(StatisticsCollection::class)
            ->setMethods(['addFilterByEventIds', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addFilterByEventIds')
            ->with([$eventId])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($collectionItemMock);
        $this->statisticsCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $statisticsMock = $this->getStatisticsMock(0, 0, 0);
        $this->statisticsInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statisticsMock);

        $this->assertEquals($statisticsMock, $this->model->getByCampaignId($campaignId));
    }

    /**
     * Test getByEventId method
     */
    public function testGetByEventId()
    {
        $eventId = 10;

        $collectionItemMock = $this->getMockBuilder(StatisticsModel::class)
            ->setMethods(['getSent', 'getOpened', 'getClicked'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItemMock->expects($this->once())
            ->method('getSent')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getOpened')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getClicked')
            ->willReturn(0);
        $collectionMock = $this->getMockBuilder(StatisticsCollection::class)
            ->setMethods(['addFilterByEventIds', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addFilterByEventIds')
            ->with([$eventId])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($collectionItemMock);
        $this->statisticsCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $statisticsMock = $this->getStatisticsMock(0, 0, 0);
        $this->statisticsInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statisticsMock);

        $this->assertEquals($statisticsMock, $this->model->getByEventId($eventId));
    }

    /**
     * Test getByEmailId method
     */
    public function testGetByEmailId()
    {
        $emailId = 10;

        $collectionItemMock = $this->getMockBuilder(StatisticsModel::class)
            ->setMethods(['getSent', 'getOpened', 'getClicked'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItemMock->expects($this->once())
            ->method('getSent')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getOpened')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getClicked')
            ->willReturn(0);
        $collectionMock = $this->getMockBuilder(StatisticsCollection::class)
            ->setMethods(['addFilterByEmailId', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addFilterByEmailId')
            ->with($emailId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($collectionItemMock);
        $this->statisticsCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $statisticsMock = $this->getStatisticsMock(0, 0, 0);
        $this->statisticsInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statisticsMock);

        $this->assertEquals($statisticsMock, $this->model->getByEmailId($emailId));
    }

    /**
     * Test getByEmailContentId method
     */
    public function testGetByEmailContentId()
    {
        $emailContentId = 10;

        $collectionItemMock = $this->getMockBuilder(StatisticsModel::class)
            ->setMethods(['getSent', 'getOpened', 'getClicked'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionItemMock->expects($this->once())
            ->method('getSent')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getOpened')
            ->willReturn(0);
        $collectionItemMock->expects($this->once())
            ->method('getClicked')
            ->willReturn(0);
        $collectionMock = $this->getMockBuilder(StatisticsCollection::class)
            ->setMethods(['addFilterByEmailContentId', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addFilterByEmailContentId')
            ->with($emailContentId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($collectionItemMock);
        $this->statisticsCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $statisticsMock = $this->getStatisticsMock(0, 0, 0);
        $this->statisticsInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statisticsMock);

        $this->assertEquals($statisticsMock, $this->model->getByEmailContentId($emailContentId));
    }

    /**
     * Get statistics mock
     *
     * @param int $sent
     * @param int $opened
     * @param int $clicked
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStatisticsMock($sent, $opened, $clicked)
    {
        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $statisticsMock->expects($this->once())
            ->method('setSent')
            ->with($sent)
            ->willReturnSelf();
        $statisticsMock->expects($this->once())
            ->method('setOpened')
            ->with($opened)
            ->willReturnSelf();
        $statisticsMock->expects($this->once())
            ->method('setClicked')
            ->with($clicked)
            ->willReturnSelf();
        $statisticsMock->expects($this->once())
            ->method('setOpenRate')
            ->with($this->greaterThanOrEqual(0))
            ->willReturnSelf();
        $statisticsMock->expects($this->once())
            ->method('setClickRate')
            ->with($this->greaterThanOrEqual(0))
            ->willReturnSelf();

        return $statisticsMock;
    }

    /**
     * Test updateByCampaignId method
     */
    public function testUpdateByCampaignId()
    {
        $campaignId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByCampaignIds')
            ->with($campaignId)
            ->willReturn(true);

        $this->assertTrue($this->model->updateByCampaignId($campaignId));
    }

    /**
     * Test updateByCampaignId method if an error occurs
     */
    public function testUpdateByCampaignIdError()
    {
        $campaignId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByCampaignIds')
            ->with($campaignId)
            ->willReturn(false);

        $this->assertFalse($this->model->updateByCampaignId($campaignId));
    }

    /**
     * Test updateByEventId method
     */
    public function testUpdateByEventId()
    {
        $eventId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEventIds')
            ->with($eventId)
            ->willReturn(true);

        $this->assertTrue($this->model->updateByEventId($eventId));
    }

    /**
     * Test updateByEventId method if an error occurs
     */
    public function testUpdateByEventIdError()
    {
        $eventId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEventIds')
            ->with($eventId)
            ->willReturn(false);

        $this->assertFalse($this->model->updateByEventId($eventId));
    }

    /**
     * Test updateByEmailId method
     */
    public function testUpdateByEmailId()
    {
        $emailId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailIds')
            ->with($emailId)
            ->willReturn(true);

        $this->assertTrue($this->model->updateByEmailId($emailId));
    }

    /**
     * Test updateByEmailId method if an error occurs
     */
    public function testUpdateByEmailIdError()
    {
        $emailId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailIds')
            ->with($emailId)
            ->willReturn(false);

        $this->assertFalse($this->model->updateByEmailId($emailId));
    }

    /**
     * Test updateByEmailContentId method
     */
    public function testUpdateByEmailContentId()
    {
        $emailContentId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(true);

        $this->assertTrue($this->model->updateByEmailContentId($emailContentId));
    }

    /**
     * Test updateByEmailContentId method if an error occurs
     */
    public function testUpdateByEmailContentIdError()
    {
        $emailContentId = 1;

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(false);

        $this->assertFalse($this->model->updateByEmailContentId($emailContentId));
    }

    /**
     * Test resetByCampaignId method
     */
    public function testResetByCampaignId()
    {
        $campaignId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByCampaignIds')
            ->with($campaignId)
            ->willReturn(true);

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByCampaignIds')
            ->with($campaignId)
            ->willReturn(true);

        $this->assertTrue($this->model->resetByCampaignId($campaignId));
    }

    /**
     * Test resetByCampaignId method if an error occurs
     */
    public function testResetByCampaignIdError()
    {
        $campaignId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByCampaignIds')
            ->with($campaignId)
            ->willReturn(false);

        $this->assertFalse($this->model->resetByCampaignId($campaignId));
    }

    /**
     * Test resetByEventId method
     */
    public function testResetByEventId()
    {
        $eventId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByEventIds')
            ->with($eventId)
            ->willReturn(true);

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEventIds')
            ->with($eventId)
            ->willReturn(true);

        $this->assertTrue($this->model->resetByEventId($eventId));
    }

    /**
     * Test resetByEventId method if an error occurs
     */
    public function testResetByEventIdError()
    {
        $eventId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByEventIds')
            ->with($eventId)
            ->willReturn(false);

        $this->assertFalse($this->model->resetByEventId($eventId));
    }

    /**
     * Test resetByEmailId method
     */
    public function testResetByEmailId()
    {
        $emailId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByEmailIds')
            ->with($emailId)
            ->willReturn(true);

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailIds')
            ->with($emailId)
            ->willReturn(true);

        $this->assertTrue($this->model->resetByEmailId($emailId));
    }

    /**
     * Test resetByEmailId method if an error occurs
     */
    public function testResetByEmailIdError()
    {
        $emailId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByEmailIds')
            ->with($emailId)
            ->willReturn(false);

        $this->assertFalse($this->model->resetByEmailId($emailId));
    }

    /**
     * Test resetByEmailContentId method
     */
    public function testResetByEmailContentId()
    {
        $emailContentId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(true);

        $this->statisticsUpdaterMock->expects($this->once())
            ->method('updateByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(true);

        $this->assertTrue($this->model->resetByEmailContentId($emailContentId));
    }

    /**
     * Test resetByEmailContentId method if an error occurs
     */
    public function testResetByEmailContentIdError()
    {
        $emailContentId = 1;

        $this->statisticsHistoryUpdaterMock->expects($this->once())
            ->method('deleteByEmailContentIds')
            ->with($emailContentId)
            ->willReturn(false);

        $this->assertFalse($this->model->resetByEmailContentId($emailContentId));
    }
}
