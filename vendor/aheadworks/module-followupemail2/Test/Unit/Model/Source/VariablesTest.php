<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source;

use Aheadworks\Followupemail2\Model\Source\Variables;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Variables
 */
class VariablesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Variables
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
            Variables::class,
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
