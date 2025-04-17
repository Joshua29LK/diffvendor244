<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Indexer;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Full as ScheduledEmailsActionFull;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Rows as ScheduledEmailsActionRows;
use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Row as ScheduledEmailsActionRow;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails
 */
class ScheduledEmailsTest extends TestCase
{
    /**
     * @var ScheduledEmails
     */
    private $model;

    /**
     * @var ScheduledEmailsActionFull|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledEmailsActionFullMock;

    /**
     * @var ScheduledEmailsActionRows|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledEmailsActionRowsMock;

    /**
     * @var ScheduledEmailsActionRow|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledEmailsActionRowMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->scheduledEmailsActionFullMock = $this->getMockBuilder(ScheduledEmailsActionFull::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledEmailsActionRowsMock = $this->getMockBuilder(ScheduledEmailsActionRows::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledEmailsActionRowMock = $this->getMockBuilder(ScheduledEmailsActionRow::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            ScheduledEmails::class,
            [
                'scheduledEmailsActionFull' => $this->scheduledEmailsActionFullMock,
                'scheduledEmailsActionRows' => $this->scheduledEmailsActionRowsMock,
                'scheduledEmailsActionRow' => $this->scheduledEmailsActionRowMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $ids = [1];

        $this->scheduledEmailsActionRowsMock->expects($this->once())
            ->method('execute')
            ->with($ids)
            ->willReturnSelf();

        $this->model->execute($ids);
    }

    /**
     * Test executeFull method
     */
    public function testExecuteFull()
    {
        $this->scheduledEmailsActionFullMock->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $this->model->executeFull();
    }

    /**
     * Test executeList method
     */
    public function testExecuteList()
    {
        $ids = [1];

        $this->scheduledEmailsActionRowsMock->expects($this->once())
            ->method('execute')
            ->with($ids)
            ->willReturnSelf();

        $this->model->executeList($ids);
    }

    /**
     * Test executeRow method
     */
    public function testExecuteRow()
    {
        $id = 1;

        $this->scheduledEmailsActionRowMock->expects($this->once())
            ->method('execute')
            ->with($id)
            ->willReturnSelf();

        $this->model->executeRow($id);
    }
}
