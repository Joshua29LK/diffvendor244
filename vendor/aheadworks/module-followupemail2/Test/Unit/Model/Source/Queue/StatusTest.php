<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Queue;

use Aheadworks\Followupemail2\Model\Source\Queue\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Queue\Status
 */
class StatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Status
     */
    private $model;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Status::class,
            []
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
