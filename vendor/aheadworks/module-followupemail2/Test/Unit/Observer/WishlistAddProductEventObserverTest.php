<?php
namespace Aheadworks\Followupemail2\Test\Unit\Observer;

use Aheadworks\Followupemail2\Observer\WishlistAddProductEventObserver;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventHistoryManagementInterface;
use Aheadworks\Followupemail2\Model\Config;
use Magento\Framework\Event;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Event\Observer;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Item as WishlistItem;

/**
 * Test for \Aheadworks\Followupemail2\Observer\WishlistAddProductEventObserver
 */
class WishlistAddProductEventObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WishlistAddProductEventObserver
     */
    private $observer;

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
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->eventHistoryManagementMock = $this->getMockBuilder(EventHistoryManagementInterface::class)
            ->getMockForAbstractClass();

        $this->observer = $objectManager->getObject(
            WishlistAddProductEventObserver::class,
            [
                'config' => $this->configMock,
                'customerRepository' => $this->customerRepositoryMock,
                'eventHistoryManagement' => $this->eventHistoryManagementMock,
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $isEnabled = true;
        $customerId = 1;
        $wishlistId = 2;
        $wishlistItemId = 10;
        $customerEmail = 'email@example.com';
        $customerFirstname = 'Fname';
        $customerLastname = 'Lname';
        $customerGroupId = 3;
        $productId = 4;
        $storeId = 5;
        $wishlistData = [
            'wishlist_id' => $wishlistId
        ];

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->setMethods(['getCustomerId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $wishlistMock->expects($this->once())
            ->method('getData')
            ->willReturn($wishlistData);

        $wishlistItemMock = $this->getMockBuilder(WishlistItem::class)
            ->setMethods(['getId', 'getStoreId', 'getProductId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemMock->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistItemId);
        $wishlistItemMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $wishlistItemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getProduct', 'getWishlist', 'getItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlistMock);
        $eventMock->expects($this->once())
            ->method('getItem')
            ->willReturn($wishlistItemMock);

        $observerMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

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
                    'email'             => $customerEmail,
                    'customer_name'     => $customerFirstname . ' ' . $customerLastname,
                    'customer_group_id' => $customerGroupId,
                    'wishlist_id'       => $wishlistId,
                    'wishlist_item_id'  => $wishlistItemId,
                    'store_id'          => $storeId
                ]
            )
            ->willReturn(true);

        $this->assertEquals($this->observer, $this->observer->execute($observerMock));
    }

    /**
     * Test execute method if the module is disabled
     */
    public function testAfterAfterSaveModuleDisabled()
    {
        $isEnabled = false;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $observerMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->observer, $this->observer->execute($observerMock));
    }

    /**
     * Test execute method if no wishlist specified
     */
    public function testExecuteNoWishlist()
    {
        $isEnabled = true;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getProduct', 'getWishlist'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);

        $observerMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals($this->observer, $this->observer->execute($observerMock));
    }

    /**
     * Test execute method if no wishlist item specified
     */
    public function testExecuteNoWishlistItem()
    {
        $isEnabled = true;
        $customerId = 1;

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->setMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getWishlist', 'getItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlistMock);
        $eventMock->expects($this->once())
            ->method('getItem')
            ->willReturn(null);

        $observerMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals($this->observer, $this->observer->execute($observerMock));
    }

    /**
     * Test execute method if no customer found
     */
    public function testExecuteNoCustomer()
    {
        $isEnabled = true;
        $customerId = 1;
        $productId = 2;
        $exceptionMessage = 'No such entity!';

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn($isEnabled);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->setMethods(['getCustomerId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $wishlistItemMock = $this->getMockBuilder(WishlistItem::class)
            ->setMethods(['getProductId'])
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getWishlist', 'getItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlistMock);
        $eventMock->expects($this->once())
            ->method('getItem')
            ->willReturn($wishlistItemMock);

        $observerMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new NoSuchEntityException(__($exceptionMessage)));

        $this->eventHistoryManagementMock->expects($this->never())
            ->method('addEvent');

        $this->assertEquals($this->observer, $this->observer->execute($observerMock));
    }
}
