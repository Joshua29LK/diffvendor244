<?php
namespace Aheadworks\Followupemail2\Test\Unit\Block\Adminhtml\Campaign;

use Aheadworks\Followupemail2\Block\Adminhtml\Campaign\CreateButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Block\Adminhtml\Campaign\CreateButton
 */
class CreateButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreateButton
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
            CreateButton::class,
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
