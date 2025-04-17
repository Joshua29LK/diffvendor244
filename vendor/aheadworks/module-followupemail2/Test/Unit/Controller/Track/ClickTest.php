<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Track;

use Aheadworks\Followupemail2\Controller\Track\Click;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Model\Statistics\EmailTracker\Encryptor as EmailTrackerEncryptor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Track\Click
 */
class ClickTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Click
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var StatisticsManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statisticsManagementMock;

    /**
     * @var EmailTrackerEncryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailTrackerEncryptorMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

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

        $this->statisticsManagementMock = $this->getMockBuilder(StatisticsManagementInterface::class)
            ->getMockForAbstractClass();

        $this->emailTrackerEncryptorMock = $this->getMockBuilder(EmailTrackerEncryptor::class)
            ->setMethods(['decrypt'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Click::class,
            [
                'context' => $this->contextMock,
                'statisticsManagement' => $this->statisticsManagementMock,
                'emailTrackerEncryptor' => $this->emailTrackerEncryptorMock
            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $url = 'http://example.com';
        $encodedUrl = urlencode(urlencode($url));
        $params = 'K3joCAvXO3BxzIWYNF3rhn5xQjHMsRF8';
        $decryptedParams = [
            'stat_id'   => 123,
            'email'     => 'test@example.com'
        ];

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('url')
            ->willReturn($encodedUrl);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('params')
            ->willReturn($params);

        $this->emailTrackerEncryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($params)
            ->willReturn($decryptedParams);

        $this->statisticsManagementMock->expects($this->once())
            ->method('addClicked')
            ->with($decryptedParams['stat_id'], $decryptedParams['email'])
            ->willReturn(true);

        $resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when no params specified
     */
    public function testExecuteNoParams()
    {
        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setRefererOrBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('url')
            ->willReturn(null);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('params')
            ->willReturn(null);

        $resultRedirectMock->expects($this->once())
            ->method('setRefererOrBaseUrl')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }

    /**
     * Test execute method when no url specified
     */
    public function testExecuteNoUrl()
    {
        $params = 'K3joCAvXO3BxzIWYNF3rhn5xQjHMsRF8';
        $decryptedParams = [
            'stat_id'   => 123,
            'email'     => 'test@example.com'
        ];

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setRefererOrBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('url')
            ->willReturn(null);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('params')
            ->willReturn($params);

        $this->emailTrackerEncryptorMock->expects($this->once())
            ->method('decrypt')
            ->with($params)
            ->willReturn($decryptedParams);

        $this->statisticsManagementMock->expects($this->once())
            ->method('addClicked')
            ->with($decryptedParams['stat_id'], $decryptedParams['email'])
            ->willReturn(true);

        $resultRedirectMock->expects($this->once())
            ->method('setRefererOrBaseUrl')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->controller->execute());
    }
}
