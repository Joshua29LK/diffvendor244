<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Event\Plugin;

use Aheadworks\Followupemail2\Model\Event\Plugin\WishlistItem;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Aheadworks\Followupemail2\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Wishlist\Model\Item as WishlistItemModel;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item as WishlistItemResource;

/**
 * Test for \Aheadworks\Followupemail2\Model\Event\Plugin\WishlistItem
 */
class WishlistItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WishlistItem
     */
    private $model;

    /**
     * @var EventHistoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventHistoryManagementMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var WishlistFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wishlistFactoryMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var WishlistItemResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wishlistItemResourceMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->eventHistoryManagementMock = $this->getMockBuilder(EventHistoryManagementInterface::class)
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isEnabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistFactoryMock = $this->getMockBuilder(WishlistFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->wishlistItemResourceMock = $this->getMockBuilder(WishlistItemResource::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            WishlistItem::class,
            [
                'eventHistoryManagement' => $this->eventHistoryManagementMock,
                'config' => $this->configMock,
                'wishlistFactory' => $this->wishlistFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
            ]
        );
    }

    /**
     * Test aroundDelete method
     */
    public function testAroundDelete()
    {
        $isEnabled = true;
        $wishlistId = 1;
        $storeId = 2;
        $wishlistItemId = 10;
        $customerId = 3;
        $customerEmail = 'email@example.com';
        $customerFirstname = 'Fname';
        $customerLastname = 'Lname';
        $customerGroupId = 3;

        $wishlistItemModelMock = $this->getMockBuilder(WishlistItemModel::class)
            ->setMethods(['getId', 'getWishlistId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistItemId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $clousureCalled = false;
        $proceed = function ($query) use (&$clousureCalled, $wishlistItemModelMock) {
            $clousureCalled = true;
            $this->assertEquals($wishlistItemModelMock, $query);
            return $this->wishlistItemResourceMock;
        };

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->setMethods(['load', 'getId', 'getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->once())
            ->method('load')
            ->with($wishlistId)
            ->willReturnSelf();
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($wishlistId);
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
             ->willReturn($customerId);
        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($wishlistMock);

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
                EventInterface::TYPE_WISHLIST_CONTENT_CHANGED,
                [
                    'wishlist_id' => $wishlistId,
                    'wishlist_item_id' => $wishlistItemId,
                    'email' => $customerEmail,
                    'customer_name' => $customerFirstname . ' ' . $customerLastname,
                    'customer_group_id' => $customerGroupId,
                    'store_id' => $storeId,
                    'delete_from_wishlist' => true
                ]
            )
            ->willReturn(true);

        $this->assertEquals(
            $this->wishlistItemResourceMock,
            $this->model->aroundDelete($this->wishlistItemResourceMock, $proceed, $wishlistItemModelMock)
        );
    }

    /**
     * Test aroundDelete method if module disabled
     */
    public function testAroundDeleteModuleDisabled()
    {
        $isEnabled = false;
        $wishlistId = 1;
        $storeId = 2;
        $wishlistItemId = 10;

        $wishlistItemModelMock = $this->getMockBuilder(WishlistItemModel::class)
            ->setMethods(['getId', 'getWishlistId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistItemId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $clousureCalled = false;
        $proceed = function ($query) use (&$clousureCalled, $wishlistItemModelMock) {
            $clousureCalled = true;
            $this->assertEquals($wishlistItemModelMock, $query);
            return $this->wishlistItemResourceMock;
        };

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals(
            $this->wishlistItemResourceMock,
            $this->model->aroundDelete($this->wishlistItemResourceMock, $proceed, $wishlistItemModelMock)
        );
    }

    /**
     * Test aroundDelete method if no wishlist
     */
    public function testAroundDeleteNoWishlist()
    {
        $isEnabled = true;
        $wishlistId = 1;
        $storeId = 2;
        $wishlistItemId = 10;

        $wishlistItemModelMock = $this->getMockBuilder(WishlistItemModel::class)
            ->setMethods(['getId', 'getWishlistId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistItemId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $clousureCalled = false;
        $proceed = function ($query) use (&$clousureCalled, $wishlistItemModelMock) {
            $clousureCalled = true;
            $this->assertEquals($wishlistItemModelMock, $query);
            return $this->wishlistItemResourceMock;
        };

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->setMethods(['load', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->once())
            ->method('load')
            ->with($wishlistId)
            ->willReturnSelf();
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);
        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($wishlistMock);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals(
            $this->wishlistItemResourceMock,
            $this->model->aroundDelete($this->wishlistItemResourceMock, $proceed, $wishlistItemModelMock)
        );
    }

    /**
     * Test aroundDelete method if no customer id
     */
    public function testAroundDeleteNoCustomerId()
    {
        $isEnabled = true;
        $wishlistId = 1;
        $storeId = 2;
        $wishlistItemId = 10;

        $wishlistItemModelMock = $this->getMockBuilder(WishlistItemModel::class)
            ->setMethods(['getId', 'getWishlistId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistItemId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $clousureCalled = false;
        $proceed = function ($query) use (&$clousureCalled, $wishlistItemModelMock) {
            $clousureCalled = true;
            $this->assertEquals($wishlistItemModelMock, $query);
            return $this->wishlistItemResourceMock;
        };

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->setMethods(['load', 'getId', 'getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->once())
            ->method('load')
            ->with($wishlistId)
            ->willReturnSelf();
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($wishlistId);
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn(null);
        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($wishlistMock);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals(
            $this->wishlistItemResourceMock,
            $this->model->aroundDelete($this->wishlistItemResourceMock, $proceed, $wishlistItemModelMock)
        );
    }

    /**
     * Test aroundDelete method if no customer
     */
    public function testAroundDeleteNoCustomer()
    {
        $isEnabled = true;
        $wishlistId = 1;
        $storeId = 2;
        $wishlistItemId = 10;
        $customerId = 3;
        $exceptionMessage = 'No such entity!';

        $wishlistItemModelMock = $this->getMockBuilder(WishlistItemModel::class)
            ->setMethods(['getId', 'getWishlistId', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistItemId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);
        $wishlistItemModelMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $clousureCalled = false;
        $proceed = function ($query) use (&$clousureCalled, $wishlistItemModelMock) {
            $clousureCalled = true;
            $this->assertEquals($wishlistItemModelMock, $query);
            return $this->wishlistItemResourceMock;
        };

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->setMethods(['load', 'getId', 'getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->once())
            ->method('load')
            ->with($wishlistId)
            ->willReturnSelf();
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($wishlistId);
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($wishlistMock);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new NoSuchEntityException(__($exceptionMessage)));

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals(
            $this->wishlistItemResourceMock,
            $this->model->aroundDelete($this->wishlistItemResourceMock, $proceed, $wishlistItemModelMock)
        );
    }
}
