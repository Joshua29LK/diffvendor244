<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Event;

use Aheadworks\Followupemail2\Model\Source\Event\FailedEmailsMode;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Event\FailedEmailsMode
 */
class FailedEmailsModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FailedEmailsMode
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
            FailedEmailsMode::class,
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
