<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Model\EmailManagement;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterfaceFactory;
use Aheadworks\Followupemail2\Model\Sender;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\EmailManagement
 */
class EmailManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmailManagement
     */
    private $model;

    /**
     * @var EmailRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailRepositoryMock;

    /**
     * @var StatisticsManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsManagementMock;

    /**
     * @var PreviewInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $previewFactoryMock;

    /**
     * @var Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $senderMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->emailRepositoryMock = $this->getMockBuilder(EmailRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsManagementMock = $this->getMockBuilder(StatisticsManagementInterface::class)
            ->getMockForAbstractClass();
        $this->previewFactoryMock = $this->getMockBuilder(PreviewInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->senderMock = $this->getMockBuilder(Sender::class)
            ->setMethods(['getTestPreview'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create', 'addFilter', 'addSortOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->setMethods(['create', 'setField', 'setAscendingDirection', 'setDescendingDirection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            EmailManagement::class,
            [
                'emailRepository' => $this->emailRepositoryMock,
                'statisticsManagement' => $this->statisticsManagementMock,
                'previewFactory' => $this->previewFactoryMock,
                'sender' => $this->senderMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock
            ]
        );
    }

    /**
     * Test disableEmail method
     */
    public function testDisableEmail()
    {
        $emailId = 1;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('setStatus')
            ->with(EmailInterface::STATUS_DISABLED)
            ->willReturnSelf();

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);
        $this->emailRepositoryMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->assertEquals($emailMock, $this->model->disableEmail($emailId));
    }

    /**
     * Test disableEmail method if no such email
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testDisableEmailException()
    {
        $emailId = 1;

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willThrowException(NoSuchEntityException::singleField('id', $emailId));
        $this->expectException(NoSuchEntityException::class);
        $this->model->disableEmail($emailId);
    }

    /**
     * Test getEmailsByEventId method
     */
    public function testGetEmailsByEventId()
    {
        $eventId = 1;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(EmailInterface::POSITION)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EmailInterface::EVENT_ID, $eventId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailMock]);

        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertEquals([$emailMock], $this->model->getEmailsByEventId($eventId, false));
    }

    /**
     * Test getEmailsByEventId method if enabled only specified
     */
    public function testGetEmailsByEventIdEnabledOnly()
    {
        $eventId = 1;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(EmailInterface::POSITION)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                [EmailInterface::EVENT_ID, $eventId, 'eq'],
                [EmailInterface::STATUS, EmailInterface::STATUS_ENABLED, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailMock]);

        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertEquals([$emailMock], $this->model->getEmailsByEventId($eventId, true));
    }

    /**
     * Test getNextEmailToSend method
     *
     * @param EmailInterface[] $emailMocks
     * @param int $countOfSentEmails
     * @param EmailInterface|false $result
     * @dataProvider getNextEmailToSendDataProvider
     */
    public function testGetNextEmailToSend($emailMocks, $countOfSentEmails, $result)
    {
        $eventId = 1;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(EmailInterface::POSITION)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EmailInterface::EVENT_ID, $eventId, 'eq'],
                [EmailInterface::STATUS, EmailInterface::STATUS_ENABLED, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($emailMocks);

        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertEquals($result, $this->model->getNextEmailToSend($eventId, $countOfSentEmails));
    }

    /**
     * @return array
     */
    public function getNextEmailToSendDataProvider()
    {
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMock();

        return [
            [[], 0, false],
            [[$emailMock], 0, $emailMock],
            [[$emailMock], 1, false],
        ];
    }

    /**
     * Test changeStatus method if email is disabled
     */
    public function testChangeStatusToEnabled()
    {
        $emailId = 1;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(EmailInterface::STATUS_DISABLED);
        $emailMock->expects($this->once())
            ->method('setStatus')
            ->with(EmailInterface::STATUS_ENABLED)
            ->willReturnSelf();

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);
        $this->emailRepositoryMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->assertEquals($emailMock, $this->model->changeStatus($emailId));
    }

    /**
     * Test changeStatus method if email is enabled
     */
    public function testChangeStatusToDisabled()
    {
        $emailId = 1;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(EmailInterface::STATUS_ENABLED);
        $emailMock->expects($this->once())
            ->method('setStatus')
            ->with(EmailInterface::STATUS_DISABLED)
            ->willReturnSelf();

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);
        $this->emailRepositoryMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->assertEquals($emailMock, $this->model->changeStatus($emailId));
    }

    /**
     * Test changePosition method
     */
    public function testChangePosition()
    {
        $emailId = 1;
        $position = 10;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('setPosition')
            ->with($position)
            ->willReturnSelf();

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);
        $this->emailRepositoryMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willReturn($emailMock);

        $this->assertSame($emailMock, $this->model->changePosition($emailId, $position));
    }

    /**
     * Test changePosition method if no email can be loaded
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id=123
     */
    public function testChangePositionNoEmail()
    {
        $emailId = 1;
        $position = 10;

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willThrowException(new NoSuchEntityException(__('No such entity with id=123')));
        $this->expectException(NoSuchEntityException::class);
        $this->model->changePosition($emailId, $position);
    }

    /**
     * Test changePosition method if the email can not be saved
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testChangePositionNoEmailSaved()
    {
        $emailId = 1;
        $position = 10;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('setPosition')
            ->with($position)
            ->willReturnSelf();

        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);
        $this->emailRepositoryMock->expects($this->once())
            ->method('save')
            ->with($emailMock)
            ->willThrowException(new LocalizedException(__('Error!')));
        $this->expectException(LocalizedException::class);
        $this->model->changePosition($emailId, $position);
    }

    /**
     * Test isFirst method
     */
    public function testIsFirst()
    {
        $eventId = 1;
        $emailId = 10;
        $emailsCount = 1;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(EmailInterface::POSITION)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EmailInterface::EVENT_ID, $eventId, 'eq'],
                [EmailInterface::STATUS, EmailInterface::STATUS_ENABLED, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getId')
            ->willReturn($emailId);
        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailMock]);
        $emailSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($emailsCount);

        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertTrue($this->model->isFirst($emailId, $eventId));
    }

    /**
     * Test isFirst method if email is not first
     */
    public function testIsFirstNotFirst()
    {
        $eventId = 1;
        $firstEmailId = 10;
        $secondEmailId = 11;
        $emailsCount = 2;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(EmailInterface::POSITION)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->withConsecutive(
                [EmailInterface::EVENT_ID, $eventId, 'eq'],
                [EmailInterface::STATUS, EmailInterface::STATUS_ENABLED, 'eq']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $firstEmailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $firstEmailMock->expects($this->once())
            ->method('getId')
            ->willReturn($firstEmailId);
        $secondEmailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$firstEmailMock, $secondEmailMock]);
        $emailSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($emailsCount);

        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertFalse($this->model->isFirst($secondEmailId, $eventId));
    }

    /**
     * Test isCanBeFirst method
     *
     * @param int $emailId
     * @param EmailInterface[] $emails
     * @param bool $result
     * @dataProvider isCanBeFirstDataProvider
     */
    public function testIsCanBeFirst($emailId, $emails, $result)
    {
        $eventId = 1;
        $emailsCount = count($emails);

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(EmailInterface::POSITION)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EmailInterface::EVENT_ID, $eventId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->any())
            ->method('getItems')
            ->willReturn($emails);
        $emailSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($emailsCount);

        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertEquals($result, $this->model->isCanBeFirst($emailId, $eventId));
    }

    /**
     * @return array
     */
    public function isCanBeFirstDataProvider()
    {
        return [
            [
                'emailId' => null,
                'eventEmails' => [],
                'result' => true
            ],
            [
                'emailId' => null,
                'eventEmails' => [
                    $this->getEmailMock(10, EmailInterface::STATUS_DISABLED),
                ],
                'result' => true
            ],
            [
                'emailId' => null,
                'eventEmails' => [
                    $this->getEmailMock(10, EmailInterface::STATUS_ENABLED),
                ],
                'result' => false
            ],
            [
                'emailId' => 10,
                'eventEmails' => [
                    $this->getEmailMock(10, EmailInterface::STATUS_DISABLED),
                    $this->getEmailMock(11, EmailInterface::STATUS_ENABLED),
                    $this->getEmailMock(12, EmailInterface::STATUS_ENABLED),
                ],
                'result' => true
            ],
            [
                'emailId' => 11,
                'eventEmails' => [
                    $this->getEmailMock(10, EmailInterface::STATUS_DISABLED),
                    $this->getEmailMock(11, EmailInterface::STATUS_ENABLED),
                    $this->getEmailMock(12, EmailInterface::STATUS_ENABLED),
                ],
                'result' => true
            ],
            [
                'emailId' => 12,
                'eventEmails' => [
                    $this->getEmailMock(10, EmailInterface::STATUS_DISABLED),
                    $this->getEmailMock(11, EmailInterface::STATUS_ENABLED),
                    $this->getEmailMock(12, EmailInterface::STATUS_ENABLED),
                ],
                'result' => false
            ],
            [
                'emailId' => 12,
                'eventEmails' => [
                    $this->getEmailMock(10, EmailInterface::STATUS_DISABLED),
                    $this->getEmailMock(11, EmailInterface::STATUS_ENABLED),
                    $this->getEmailMock(12, EmailInterface::STATUS_DISABLED),
                ],
                'result' => false
            ],
            [
                'emailId' => 12,
                'eventEmails' => [
                    $this->getEmailMock(10, EmailInterface::STATUS_DISABLED),
                    $this->getEmailMock(11, EmailInterface::STATUS_DISABLED),
                    $this->getEmailMock(12, EmailInterface::STATUS_DISABLED),
                ],
                'result' => true
            ],
        ];
    }

    /**
     * @param int $id
     * @param int $status
     * @return EmailInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEmailMock($id, $status)
    {
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $emailMock->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);

        return $emailMock;
    }

    /**
     * Test getStatistics method
     */
    public function testGetStatistics()
    {
        $emailId = 1;

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->any())
            ->method('getId')
            ->willReturn($emailId);

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsManagementMock->expects($this->once())
            ->method('getByEmailId')
            ->with($emailId)
            ->willReturn($statisticsMock);

        $this->assertEquals($statisticsMock, $this->model->getStatistics($emailMock));
    }

    /**
     * Test getStatisticsByContentId method
     */
    public function testGetStatisticsByContentId()
    {
        $emailContentId = 10;

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $this->statisticsManagementMock->expects($this->once())
            ->method('getByEmailContentId')
            ->with($emailContentId)
            ->willReturn($statisticsMock);

        $this->assertEquals($statisticsMock, $this->model->getStatisticsByContentId($emailContentId));
    }

    /**
     * Test getNewEmailPosition method
     */
    public function testGetNewEmailPosition()
    {
        $eventId = 1;
        $emailPosition = 10;
        $emailsCount = 1;

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(EmailInterface::POSITION)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setDescendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('addFilter')
            ->with(EmailInterface::EVENT_ID, $eventId, 'eq')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getPosition')
            ->willReturn($emailPosition);
        $emailSearchResultsMock = $this->getMockBuilder(EmailSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $emailSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$emailMock]);
        $emailSearchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn($emailsCount);

        $this->emailRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($emailSearchResultsMock);

        $this->assertGreaterThan($emailPosition, $this->model->getNewEmailPosition($eventId));
    }

    /**
     * Test getPreview method
     */
    public function testGetPreview()
    {
        $storeId = 1;
        $senderName = 'Sender Name';
        $senderEmail = 'test@example.com';
        $recipientName = 'Recipient Name';
        $recipientEmail = 'recipient@example.com';
        $subject = 'Test subject';
        $content = 'Test content';
        $previewContent = [
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'subject' => $subject,
            'content' => $content,
        ];

        $emailContentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $emailContentMock->expects($this->once())
            ->method('getSenderName')
            ->willReturn($senderName);
        $emailContentMock->expects($this->once())
            ->method('getSenderEmail')
            ->willReturn($senderEmail);

        $this->senderMock->expects($this->once())
            ->method('getTestPreview')
            ->with($storeId, $emailContentMock)
            ->willReturn($previewContent);

        $previewMock = $this->getMockBuilder(PreviewInterface::class)
            ->getMockForAbstractClass();
        $previewMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setSenderName')
            ->with($senderName)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setSenderEmail')
            ->with($senderEmail)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setRecipientName')
            ->with($recipientName)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setRecipientEmail')
            ->with($recipientEmail)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setSubject')
            ->with($subject)
            ->willReturnSelf();
        $previewMock->expects($this->once())
            ->method('setContent')
            ->with($content)
            ->willReturnSelf();
        $this->previewFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($previewMock);

        $this->assertEquals($previewMock, $this->model->getPreview($storeId, $emailContentMock));
    }
}
