<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source;

use Aheadworks\Followupemail2\Model\Source\CustomerGroups;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Convert\DataObject as ConvertDataObject;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\CustomerGroups
 */
class CustomerGroupsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerGroups
     */
    private $model;

    /**
     * @var GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManagementMock;

    /**
     * @var ConvertDataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectConverterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->groupManagementMock = $this->getMockBuilder(GroupManagementInterface::class)
            ->getMockForAbstractClass();
        $this->objectConverterMock = $this->getMockBuilder(ConvertDataObject::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            CustomerGroups::class,
            [
                'groupManagement' => $this->groupManagementMock,
                'objectConverter' => $this->objectConverterMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $groupId = 1;
        $groupCode = 'General';
        $result = [
            ['value' => 'all', 'label' => __('All Groups')],
            ['value' => $groupId, 'label' => $groupCode],
        ];

        $groupMock = $this->getMockBuilder(GroupInterface::class)
            ->getMockForAbstractClass();
        $this->groupManagementMock->expects($this->once())
            ->method('getLoggedInGroups')
            ->willReturn([$groupMock]);
        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with([$groupMock])
            ->willReturn([['value' => $groupId, 'label' => $groupCode]]);

        $this->assertEquals($result, $this->model->toOptionArray());
    }
}
