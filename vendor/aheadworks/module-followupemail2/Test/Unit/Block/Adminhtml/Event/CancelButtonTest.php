<?php
namespace Aheadworks\Followupemail2\Test\Unit\Block\Adminhtml\Event;

use Aheadworks\Followupemail2\Block\Adminhtml\Event\CancelButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Block\Adminhtml\Event\CancelButton
 */
class CancelButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CancelButton
     */
    private $button;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);
        $this->button = $objectManager->getObject(
            CancelButton::class,
            []
        );
    }

    /**
     * Test getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
