<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Index;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Event\Queue\Index
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Index
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $objectManager->getObject(
            Context::class,
            []
        );

        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Index::class,
            [
                'context' => $this->contextMock,
                'resultPageFactory' => $this->resultPageFactoryMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $titleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $resultPageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['setActiveMenu', 'getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->willReturnSelf();
        $resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);

        $this->assertSame($resultPageMock, $this->controller->execute());
    }
}
