<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Indexer\ScheduledEmails\Action;

use Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Rows;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails as ScheduledEmailsIndexerResource;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\Followupemail2\Model\Indexer\ScheduledEmails\Action\Rows
 */
class RowsTest extends TestCase
{
    /**
     * @var Rows
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
            Rows::class,
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
        $ids = [1];

        $this->scheduledEmailsIndexerResourceMock->expects($this->once())
            ->method('reindexRows')
            ->with($ids)
            ->willReturnSelf();

        $this->model->execute($ids);
    }

    /**
     * Test execute method if no id specified
     *
     * @param $param
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Bad value was supplied.
     * @dataProvider executeNoIdDataProvider
     */
    public function testExecuteNoIdSpecified($param)
    {
         $this->expectException(InputException::class);
         $this->model->execute($param);
    }

    /**
     * @return array
     */
    public function executeNoIdDataProvider()
    {
        return [
            [null], [1]
        ];
    }

    /**
     * Test execute method if an error occurs
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!!!
     */
    public function testExecuteError()
    {
        $ids = [1];
        $errorMessage = __('Error!!!');

        $this->scheduledEmailsIndexerResourceMock->expects($this->once())
            ->method('reindexRows')
            ->with($ids)
            ->willThrowException(new \Exception($errorMessage));
        $this->expectException(LocalizedException::class);
        $this->model->execute($ids);
    }
}
