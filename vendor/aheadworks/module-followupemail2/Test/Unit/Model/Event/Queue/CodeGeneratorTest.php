<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Queue;

use Aheadworks\Followupemail2\Model\Event\Queue\CodeGenerator;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Collection as EventQueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\CollectionFactory as EventQueueCollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Math\Random;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Queue\CodeGenerator
 */
class CodeGeneratorTest extends \PHPUnit\Framework\TestCase
{
    const CODE_LENGTH = 32;

    /**
     * @var CodeGenerator
     */
    private $model;

    /**
     * @var EventQueueCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventQueueCollectionFactoryMock;

    /**
     * @var Random|\PHPUnit_Framework_MockObject_MockObject
     */
    private $randomMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->eventQueueCollectionFactoryMock = $this->getMockBuilder(EventQueueCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->randomMock = $this->getMockBuilder(Random::class)
            ->setMethods(['getRandomString'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            CodeGenerator::class,
            [
                'eventQueueCollectionFactory' => $this->eventQueueCollectionFactoryMock,
                'random' => $this->randomMock,
            ]
        );
    }

    /**
     * Test getCode method
     */
    public function testGetCode()
    {
        $randomString = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabc0';

        $this->randomMock->expects($this->exactly(2))
            ->method('getRandomString')
            ->with(self::CODE_LENGTH)
            ->willReturn($randomString);

        $eventQueueCollectionMock = $this->getMockBuilder(EventQueueCollection::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $eventQueueCollectionMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                [EventQueueInterface::SECURITY_CODE, $randomString, 'eq'],
                [EventQueueInterface::SECURITY_CODE, $randomString, 'eq']
            )
            ->willReturnSelf();
        $eventQueueCollectionMock->expects($this->exactly(2))
            ->method('getSize')
            ->willReturnOnConsecutiveCalls(1, 0);
        $this->eventQueueCollectionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($eventQueueCollectionMock);

        $this->assertEquals($randomString, $this->model->getCode());
    }
}
