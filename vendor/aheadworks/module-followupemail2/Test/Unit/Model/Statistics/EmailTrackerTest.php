<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Statistics;

use Aheadworks\Followupemail2\Model\Statistics\EmailTracker;
use Aheadworks\Followupemail2\Model\Statistics\EmailTracker\Encryptor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Test for \Aheadworks\Followupemail2\Model\Statistics\EmailTracker
 */
class EmailTrackerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmailTracker
     */
    private $model;

    /**
     * @var Encryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encryptorMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->encryptorMock = $this->getMockBuilder(Encryptor::class)
            ->setMethods(['encrypt'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            EmailTracker::class,
            [
                'encryptor' => $this->encryptorMock,
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );
    }

    /**
     * Test getPreparedContent method
     */
    public function testGetPreparedContent()
    {
        $storeId = 1;
        $params = [
            'param1' => 1,
            'param2' => 2,
        ];
        $encryptedParams = 'ABCDEFG1234567890';
        $emailContent = '<p><strong>Hi ExampleUser,</strong></p>' .
            '<p>Enjoy shopping at <a href="http://example.com">Example Store</a>!</p>';
        $openTrackUrl = 'http://example.com/aw_followupemail2/track/open';
        $clickTrackUrl = 'http://example.com/aw_followupemail2/track/click';

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $this->encryptorMock->expects($this->exactly(2))
            ->method('encrypt')
            ->with($params)
            ->willReturn($encryptedParams);

        $this->storeManagerMock->expects($this->exactly(2))
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);

        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('setScope')
            ->with($storeMock)
            ->willReturnSelf();
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls($openTrackUrl, $clickTrackUrl);

        $resultContent = $this->model->getPreparedContent($emailContent, $params, $storeId);
        $this->assertTrue(strpos($resultContent, $clickTrackUrl) !== -1 ? true : false);
        $this->assertTrue(strpos($resultContent, $openTrackUrl) !== -1 ? true : false);
    }
}
