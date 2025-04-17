<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Indexer\ScheduledEmails\Action;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Full;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails as ScheduledEmailsIndexerResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Full
 */
class FullTest extends TestCase
{
    /**
     * @var Full
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
            ->setMethods(['reindexAll'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Full::class,
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
        $this->scheduledEmailsIndexerResourceMock->expects($this->once())
            ->method('reindexAll')
            ->willReturnSelf();

        $this->model->execute();
    }

    /**
     * Test execute method if an error occurs
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!!!
     */
    public function testExecuteError()
    {
        $errorMessage = __('Error!!!');

        $this->scheduledEmailsIndexerResourceMock->expects($this->once())
            ->method('reindexAll')
                ->willThrowException(new \Exception($errorMessage));
        $this->expectException(LocalizedException::class);
        $this->model->execute();
    }
}
