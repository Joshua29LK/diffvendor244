<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Email;

use Aheadworks\Followupemail2\Model\Source\Email\Minutes;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Email\Minutes
 */
class MinutesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Minutes
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
            Minutes::class,
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
