<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Model\Source\Email\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Email\Status
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

    /**
     * Test getOptions method
     */
    public function testGetOptions()
    {
        $result = [
            EmailInterface::STATUS_DISABLED => __('Disabled'),
            EmailInterface::STATUS_ENABLED => __('Enabled'),
        ];

        $this->assertEquals($result, $this->model->getOptions());
    }

    /**
     * Test getOptionByValue method
     */
    public function testGetOptionByValue()
    {
        $this->assertEquals(__('Disabled'), $this->model->getOptionByValue(EmailInterface::STATUS_DISABLED));
    }
}
