<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Plugin;

use Aheadworks\Followupemail2\Model\Event\Plugin\Review;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Aheadworks\Followupemail2\Model\Config;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Review\Model\Review as ReviewModel;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Plugin\Review
 */
class ReviewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Review
     */
    private $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var EventHistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryManagementMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isEnabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventHistoryManagementMock = $this->getMockBuilder(EventHistoryManagementInterface::class)
            ->getMockForAbstractClass();
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            Review::class,
            [
                'config' => $this->configMock,
                'eventHistoryManagement' => $this->eventHistoryManagementMock,
                'customerRepository' => $this->customerRepositoryMock,
            ]
        );
    }

    /**
     * Test afterAfterSave method
     */
    public function testAfterAfterSave()
    {
        $isEnabled = true;
        $isApproved = true;
        $customerId = 1;
        $reviewId = 2;
        $customerEmail = 'email@example.com';
        $customerFirstname = 'Fname';
        $customerLastname = 'Lname';
        $customerGroupId = 3;
        $productId = 4;
        $reviewData = [
            'review_id' => $reviewId
        ];

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $reviewModelMock = $this->getMockBuilder(ReviewModel::class)
            ->setMethods(['getCustomerId', 'isApproved', 'getEntityPkValue', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('isApproved')
            ->willReturn($isApproved);
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('getEntityPkValue')
            ->willReturn($productId);
        $reviewModelMock->expects($this->once())
            ->method('getData')
            ->willReturn($reviewData);

        $customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->getMockForAbstractClass();
        $customerMock->expects($this->once())
            ->method('getEmail')
            ->willReturn($customerEmail);
        $customerMock->expects($this->once())
            ->method('getFirstname')
            ->willReturn($customerFirstname);
        $customerMock->expects($this->once())
            ->method('getLastname')
            ->willReturn($customerLastname);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($customerGroupId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($customerMock);

        $this->eventHistoryManagementMock->expects($this->once())
            ->method('addEvent')
            ->with(
                EventInterface::TYPE_CUSTOMER_REVIEW,
                [
                    'review_id' => $reviewId,
                    'email' => $customerEmail,
                    'customer_name' => $customerFirstname . ' ' . $customerLastname,
                    'customer_group_id' => $customerGroupId,
                    'product_id' => $productId,
                ]
            )
            ->willReturn(true);

        $this->assertEquals($reviewModelMock, $this->model->afterAfterSave($reviewModelMock, $reviewModelMock));
    }

    /**
     * Test afterAfterSave method if the module is disabled
     */
    public function testAfterAfterSaveModuleDisabled()
    {
        $isEnabled = false;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $reviewModelMock = $this->getMockBuilder(ReviewModel::class)
            ->setMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals($reviewModelMock, $this->model->afterAfterSave($reviewModelMock, $reviewModelMock));
    }

    /**
     * Test afterAfterSave method if no customer id specified
     */
    public function testAfterAfterSaveNoCustomerId()
    {
        $isEnabled = true;
        $customerId = null;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $reviewModelMock = $this->getMockBuilder(ReviewModel::class)
            ->setMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals($reviewModelMock, $this->model->afterAfterSave($reviewModelMock, $reviewModelMock));
    }

    /**
     * Test afterAfterSave method if a review is not approved
     */
    public function testAfterAfterSaveNotApprovedCustomer()
    {
        $isEnabled = true;
        $isApproved = false;
        $customerId = 1;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $reviewModelMock = $this->getMockBuilder(ReviewModel::class)
            ->setMethods(['getCustomerId', 'isApproved'])
            ->disableOriginalConstructor()
            ->getMock();
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('isApproved')
            ->willReturn($isApproved);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals($reviewModelMock, $this->model->afterAfterSave($reviewModelMock, $reviewModelMock));
    }

    /**
     * Test afterAfterSave method if no customer found
     */
    public function testAfterAfterSaveNoCustomer()
    {
        $isEnabled = true;
        $isApproved = true;
        $customerId = 1;
        $exceptionMessage = 'No such entity!';

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $reviewModelMock = $this->getMockBuilder(ReviewModel::class)
            ->setMethods(['getCustomerId', 'isApproved'])
            ->disableOriginalConstructor()
            ->getMock();
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $reviewModelMock->expects($this->atLeastOnce())
            ->method('isApproved')
            ->willReturn($isApproved);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new NoSuchEntityException(__($exceptionMessage)));

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals($reviewModelMock, $this->model->afterAfterSave($reviewModelMock, $reviewModelMock));
    }
}
