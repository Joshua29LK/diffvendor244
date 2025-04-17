<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Event;

use Aheadworks\Followupemail2\Model\Source\Event\Queue\AbTestingMode;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Event\Queue\AbTestingModeTest
 */
class AbTestingModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AbTestingMode
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
            AbTestingMode::class,
            []
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertIsArray($this->model->toOptionArray());
    }
}
