<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Product;

use Aheadworks\Followupemail2\Controller\Product\Image;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Product\Image
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Image
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var ImageHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageHelperMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var AssetRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );

        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->imageHelperMock = $this->getMockBuilder(ImageHelper::class)
            ->setMethods(['init', 'getUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetRepositoryMock = $this->getMockBuilder(AssetRepository::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Image::class,
            [
                'context' => $this->contextMock,
                'productRepository' => $this->productRepositoryMock,
                'imageHelper' => $this->imageHelperMock,
                'assetRepository' => $this->assetRepositoryMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $productId = 123;
        $width = 50;
        $height = 100;
        $imageUrl = 'http://example.com/pub/static/frontend/Magento/luma/en_US/image.gif';

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('product_id')
            ->willReturn($productId);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('width')
            ->willReturn($width);
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('height')
            ->willReturn($height);

        $productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->imageHelperMock->expects($this->once())
            ->method('init')
            ->with(
                $productMock,
                'product_thumbnail_image',
                [
                    'aspect_ratio'  => true,
                    'width'         => $width,
                    'height'        => $height
                ]
            )
            ->willReturnSelf();
        $this->imageHelperMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($imageUrl);

        $resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($imageUrl)
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when no product found
     */
    public function testExecuteNoProduct()
    {
        $productId = 123;
        $width = 50;
        $height = 100;
        $imageName = 'spacer.gif';
        $imageUrl = 'http://example.com/pub/static/frontend/Magento/luma/en_US/spacer.gif';

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('product_id')
            ->willReturn($productId);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('width')
            ->willReturn($width);
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('height')
            ->willReturn($height);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new NoSuchEntityException());

        $this->assetRepositoryMock->expects($this->once())
            ->method('getUrl')
            ->with($imageName)
            ->willReturn($imageUrl);

        $resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($imageUrl)
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when no product id specified
     */
    public function testExecuteNoProductId()
    {
        $imageName = 'spacer.gif';
        $imageUrl = 'http://example.com/pub/static/frontend/Magento/luma/en_US/spacer.gif';

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('product_id')
            ->willReturn(null);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('width')
            ->willReturn(null);
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('height')
            ->willReturn(null);

        $this->assetRepositoryMock->expects($this->once())
            ->method('getUrl')
            ->with($imageName)
            ->willReturn($imageUrl);

        $resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($imageUrl)
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }
}
