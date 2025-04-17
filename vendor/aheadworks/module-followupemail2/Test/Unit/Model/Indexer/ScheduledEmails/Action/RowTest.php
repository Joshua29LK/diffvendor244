<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Indexer\ScheduledEmails\Action;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Row;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails as ScheduledEmailsIndexerResource;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Row
 */
class RowTest extends TestCase
{
    /**
     * @var Row
     */
    private $model;

    /**
     * @var ScheduledEmailsIndexerResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledEmailsIndexerResourceMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->scheduledEmailsIndexerResourceMock = $this->getMockBuilder(ScheduledEmailsIndexerResource::class)
            ->setMethods(['reindexRows'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Row::class,
            [
                'scheduledEmailsIndexerResource' => $this->scheduledEmailsIndexerResourceMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $id = 1;

        $this->scheduledEmailsIndexerResourceMock->expects($this->once())
            ->method('reindexRows')
            ->with([$id])
            ->willReturnSelf();

        $this->model->execute($id);
    }

    /**
     * Test execute method if no id specified
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage We can't rebuild the index for an undefined entity.
     */
    public function testExecuteNoIdSpecified()
    {
        $this->expectException(InputException::class);
        $this->model->execute();
    }

    /**
     * Test execute method if an error occurs
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!!!
     */
    public function testExecuteError()
    {
        $id = 1;
        $errorMessage = __('Error!!!');

        $this->scheduledEmailsIndexerResourceMock->expects($this->once())
            ->method('reindexRows')
            ->with([$id])
            ->willThrowException(new \Exception($errorMessage));
        $this->expectException(LocalizedException::class);
        $this->model->execute($id);
    }
}
