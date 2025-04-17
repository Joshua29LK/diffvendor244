<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Email;

use Aheadworks\Followupemail2\Model\Source\Email\Hours;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Email\Hours
 */
class HoursTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Hours
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
            Hours::class,
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
