<?php
namespace Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Campaign;

use Aheadworks\Followupemail2\Controller\Adminhtml\Campaign\Save;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Test for \Aheadworks\Followupemail2\Test\Unit\Controller\Adminhtml\Campaign\Save
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Save
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var CampaignRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignRepositoryMock;

    /**
     * @var CampaignInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var array
     */
    private $formData = [
        'id' => '10',
        'name' => 'Test campaign',
        'description' => 'Test description',
        'status' => '1',
        'start_date' => '',
        'end_date' => ''
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getPostValue']
        );

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
            ]
        );

        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->campaignRepositoryMock = $this->getMockForAbstractClass(CampaignRepositoryInterface::class);
        $this->campaignFactoryMock = $this->getMockBuilder(CampaignInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $objectManager->getObject(
            Save::class,
            [
                'context' => $this->contextMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'campaignRepository' => $this->campaignRepositoryMock,
                'campaignFactory' => $this->campaignFactoryMock,

            ]
        );
    }

    /**
     * Test execute method
     */
    public function testExecute()
    {
        $result =  [
            'error'     => false,
            'message'   => __('Success.')
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($this->formData);

        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->formData['id'])
            ->willReturn($campaignMock);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('save')
            ->with($campaignMock)
            ->willReturn($campaignMock);

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method when no data specified
     */
    public function testExecuteNoData()
    {
        $result =  [
            'error'     => true,
            'message'   => __('No data specified!')
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn(null);

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method when no campaign to update
     */
    public function testExecuteNoCampaign()
    {
        $exception = new NoSuchEntityException();
        $result =  [
            'error'     => true,
            'message'   => __('No such entity.')
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($this->formData);

        $this->campaignRepositoryMock->expects($this->once())
            ->method('get')
            ->with($this->formData['id'])
            ->willThrowException($exception);

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }

    /**
     * Test execute method, new campaign save
     */
    public function testExecuteNewCampaign()
    {
        unset($this->formData['id']);
        $result =  [
            'error'     => false,
            'message'   => __('Success.')
        ];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($this->formData);

        $campaignMock = $this->getMockForAbstractClass(CampaignInterface::class);
        $this->campaignFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($campaignMock);

        $this->campaignRepositoryMock->expects($this->once())
            ->method('save')
            ->with($campaignMock)
            ->willReturn($campaignMock);

        $resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with($result)
            ->willReturnSelf();

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $this->assertSame($resultJsonMock, $this->controller->execute());
    }
}
