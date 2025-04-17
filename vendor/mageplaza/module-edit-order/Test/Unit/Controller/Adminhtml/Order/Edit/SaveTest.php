<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Test\Unit\Controller\Adminhtml\Order\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResource;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Mageplaza\EditOrder\Block\Adminhtml\Logs\Order\PaymentMethod;
use Mageplaza\EditOrder\Controller\Adminhtml\Order\Edit\Save;
use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\LogsFactory;
use Mageplaza\EditOrder\Model\Order\Edit as EditModel;
use Mageplaza\EditOrder\Model\Order\SaveOther;
use Mageplaza\EditOrder\Model\Order\Total as OrderTotal;
use Mageplaza\EditOrder\Model\Quote\QuoteManagement;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class SaveTest
 * @package Mageplaza\EditOrder\Test\Unit\Controller\Adminhtml\Order\Edit
 */
//TODO Fix this
class SaveTest extends TestCase
{
    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var ResourceOrder|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderResourceModel;

    /**
     * @var JsonFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactory;

    /**
     * @var PaymentFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentFactory;

    /**
     * @var PaymentResource|PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentResource;

    /**
     * @var LayoutFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultLayoutFactory;

    /**
     * @var OrderFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var LogsFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $logsFactory;

    /**
     * @var Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $authSession;

    /**
     * @var RemoteAddress|PHPUnit_Framework_MockObject_MockObject
     */
    protected $remoteAddress;

    /**
     * @var QuoteFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactory;

    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperData;

    /**
     * @var OrderTotal|PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderTotal;

    /**
     * @var EditModel|PHPUnit_Framework_MockObject_MockObject
     */
    protected $editModel;

    /**
     * @var QuoteSession|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteSession;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Rule
     */
    private $saleRule;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var SaveOther
     */
    protected $saveOther;

    /**
     * @var PaymentMethod
     */
    protected $logsPayment;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var Save|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp():void
    {
        $this->context             = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->orderResourceModel  = $this->getMockBuilder(ResourceOrder::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultJsonFactory   = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->paymentResource     = $this->getMockBuilder(PaymentResource::class)
            ->disableOriginalConstructor()->getMock();
        $this->paymentFactory      = $this->getMockBuilder(PaymentFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultLayoutFactory = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderFactory        = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->logsFactory         = $this->getMockBuilder(LogsFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->authSession         = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->remoteAddress       = $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()->getMock();
        $this->coupon              = $this->getMockBuilder(Coupon::class)
            ->disableOriginalConstructor()->getMock();
        $this->saleRule            = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()->getMock();
        $this->invoiceFactory      = $this->getMockBuilder(InvoiceFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->saveOther           = $this->getMockBuilder(SaveOther::class)
            ->disableOriginalConstructor()->getMock();
        $this->logsPayment         = $this->getMockBuilder(PaymentMethod::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderItemFactory    = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteManagement     = $this->getMockBuilder(QuoteManagement::class)
            ->disableOriginalConstructor()->getMock();
        $this->_productRepository  = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->itemCollectionFactory= $this->getMockBuilder(ItemCollectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->_logger             = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->quoteFactory        = $this->getMockBuilder(QuoteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->_helperData         = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderTotal          = $this->getMockBuilder(OrderTotal::class)->disableOriginalConstructor()->getMock();
        $this->editModel           = $this->getMockBuilder(EditModel::class)->disableOriginalConstructor()->getMock();
        $this->quoteSession        = $this->getMockBuilder(QuoteSession::class)
            ->disableOriginalConstructor()->getMock();
        $this->_request            = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->context->method('getRequest')->willReturn($this->_request);

        $this->object = new Save(
            $this->context,
            $this->resultJsonFactory,
            $this->paymentResource,
            $this->paymentFactory,
            $this->resultLayoutFactory,
            $this->orderFactory,
            $this->coupon,
            $this->saleRule,
            $this->logsFactory,
            $this->authSession,
            $this->remoteAddress,
            $this->quoteFactory,
            $this->invoiceFactory,
            $this->_helperData,
            $this->orderTotal,
            $this->saveOther,
            $this->editModel,
            $this->quoteSession,
            $this->logsPayment,
            $this->_logger,
            $this->orderItemFactory,
            $this->quoteManagement,
            $this->_productRepository,
            $this->itemCollectionFactory
        );
    }

    /**
     * @inheritDoc
     */
    public function testAdminInstance()
    {
        $this->assertInstanceOf(Save::class, $this->object);
    }

    /**
     * unit test ApplyCoupon function()
     */
    public function testApplyCoupon()
    {
        $data = [
            'mp_coupon_code' => 'test1'
        ];

        $order = $this->getMockBuilder(Order::class)->setMethods([
            'save',
            'setCouponCode'
        ])->disableOriginalConstructor()->getMock();

        $order->method('save');

        $this->assertEquals(['success' => true], $this->object->applyCoupon($data, $order));
    }
}
