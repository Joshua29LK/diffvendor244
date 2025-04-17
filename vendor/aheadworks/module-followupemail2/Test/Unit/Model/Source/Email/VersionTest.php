<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Email;

use Aheadworks\Followupemail2\Model\Source\Email\Version;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Email\Version
 */
class VersionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Version
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
            Version::class,
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
