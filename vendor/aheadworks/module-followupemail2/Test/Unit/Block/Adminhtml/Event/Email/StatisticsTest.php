<?php
namespace Aheadworks\Followupemail2\Test\Unit\Block\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Block\Adminhtml\Event\Email\Statistics;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Helper\Data as HelperData;

/**
 * Test for \Aheadworks\Followupemail2\Block\Adminhtml\Event\Email\Statistics
 */
class StatisticsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Statistics
     */
    private $block;

    /**
     * @var EmailRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailRepositoryMock;

    /**
     * @var EmailManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailManagementMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var HelperData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperDataMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['isSecure'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->helperDataMock = $this->getMockBuilder(HelperData::class)
            ->setMethods(['getUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'helper'    => $this->helperDataMock,
            ]
        );

        $this->emailRepositoryMock = $this->getMockBuilder(EmailRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->emailManagementMock = $this->getMockBuilder(EmailManagementInterface::class)
            ->getMockForAbstractClass();

        $this->block = $objectManager->getObject(
            Statistics::class,
            [
                'context' => $this->contextMock,
                'emailRepository'   => $this->emailRepositoryMock,
                'emailManagement'   => $this->emailManagementMock,
                'data' => []
            ]
        );
    }

    /**
     * Test getEmailContentStatisticsData method
     */
    public function testGetEmailContentStatisticsData()
    {
        $emailId = 1;
        $contentId = 1;
        $result = [
            'version'       => $contentId,
            'sent'          => 3,
            'opened'        => 2,
            'clicks'        => 1,
            'open_rate'     => 66.66,
            'click_rate'    => 50.00,
            'inactive'      => false
        ];

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($emailId);

        $contentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();
        $contentMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($contentId);
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->once())
            ->method('getAbTestingMode')
            ->willReturn(false);
        $emailMock->expects($this->once())
            ->method('getPrimaryEmailContent')
            ->willReturn(EmailInterface::CONTENT_VERSION_A);
        $emailMock->expects($this->once())
            ->method('getContent')
            ->willReturn([$contentMock]);
        $this->emailRepositoryMock->expects($this->once())
            ->method('get')
            ->with($emailId)
            ->willReturn($emailMock);

        $statisticsMock = $this->getMockBuilder(StatisticsInterface::class)
            ->getMockForAbstractClass();
        $statisticsMock->expects($this->once())
            ->method('getSent')
            ->willReturn($result['sent']);
        $statisticsMock->expects($this->once())
            ->method('getOpened')
            ->willReturn($result['opened']);
        $statisticsMock->expects($this->once())
            ->method('getClicked')
            ->willReturn($result['clicks']);
        $statisticsMock->expects($this->once())
            ->method('getOpenRate')
            ->willReturn($result['open_rate']);
        $statisticsMock->expects($this->once())
            ->method('getClickRate')
            ->willReturn($result['click_rate']);
        $this->emailManagementMock->expects($this->once())
            ->method('getStatisticsByContentId')
            ->willReturn($statisticsMock);

        $this->assertEquals([$result], $this->block->getEmailContentStatisticsData());
    }
}
