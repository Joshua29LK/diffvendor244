<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Email;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Model\Email\ContentResolver;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Email\ContentResolver
 */
class ContentResolverTest extends TestCase
{
    /**
     * @var ContentResolver
     */
    private $model;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(ContentResolver::class, []);
    }

    /**
     * Test getCurrentContent method
     * @param int $abTestingMode
     * @param int $abContentVersion
     * @param int $primaryContent
     * @param EmailContentInterface[] $content
     * @param EmailContentInterface $result
     * @dataProvider getCurrentContentDataProvider
     */
    public function testGetCurrentContent($abTestingMode, $abContentVersion, $primaryContent, $content, $result)
    {
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->any())
            ->method('getAbTestingMode')
            ->willReturn($abTestingMode);
        $emailMock->expects($this->any())
            ->method('getAbEmailContent')
            ->willReturn($abContentVersion);
        $emailMock->expects($this->any())
            ->method('getPrimaryEmailContent')
            ->willReturn($primaryContent);
        $emailMock->expects($this->any())
            ->method('getContent')
            ->willReturn($content);

        $this->assertSame($result, $this->model->getCurrentContent($emailMock));
    }

    /**
     * @return array
     */
    public function getCurrentContentDataProvider()
    {
        $contentA = $this->getContentMock();
        $contentB = $this->getContentMock();

        return [
            [
                'abTestingMode' => 0,
                'abContentVersion' => 0,
                'primaryContent' => 0,
                'content' => [$contentA, $contentB],
                'result' => $contentA
            ],
            [
                'abTestingMode' => 0,
                'abContentVersion' => 0,
                'primaryContent' => EmailInterface::CONTENT_VERSION_A,
                'content' => [$contentA, $contentB],
                'result' => $contentA
            ],
            [
                'abTestingMode' => 0,
                'abContentVersion' => 0,
                'primaryContent' => EmailInterface::CONTENT_VERSION_B,
                'content' => [$contentA, $contentB],
                'result' => $contentB
            ],
            [
                'abTestingMode' => 1,
                'abContentVersion' => 0,
                'primaryContent' => 0,
                'content' => [$contentA, $contentB],
                'result' => $contentA
            ],
            [
                'abTestingMode' => 1,
                'abContentVersion' => EmailInterface::CONTENT_VERSION_A,
                'primaryContent' => 0,
                'content' => [$contentA, $contentB],
                'result' => $contentB
            ],
            [
                'abTestingMode' => 1,
                'abContentVersion' => EmailInterface::CONTENT_VERSION_B,
                'primaryContent' => 0,
                'content' => [$contentA, $contentB],
                'result' => $contentA
            ],
        ];
    }

    /**
     * Get email content mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getContentMock()
    {
        $contentMock = $this->getMockBuilder(EmailContentInterface::class)
            ->getMockForAbstractClass();

        return $contentMock;
    }

    /**
     * Test getCurrentAbContentVersion method
     *
     * @param int $abTestingMode
     * @param int $abContentVersion
     * @param int $result
     * @dataProvider getCurrentAbContentVersionDataProvider
     */
    public function testGetCurrentAbContentVersion($abTestingMode, $abContentVersion, $result)
    {
        $emailMock = $this->getMockBuilder(EmailInterface::class)
            ->getMockForAbstractClass();
        $emailMock->expects($this->any())
            ->method('getAbTestingMode')
            ->willReturn($abTestingMode);
        $emailMock->expects($this->any())
            ->method('getAbEmailContent')
            ->willReturn($abContentVersion);

        $this->assertSame($result, $this->model->getCurrentAbContentVersion($emailMock));
    }

    /**
     * @return array
     */
    public function getCurrentAbContentVersionDataProvider()
    {
        return [
            [
                'abTestingMode' => 0,
                'abContentVersion' => 0,
                'result' => false
            ],
            [
                'abTestingMode' => 1,
                'abContentVersion' => 0,
                'result' => EmailInterface::CONTENT_VERSION_A
            ],
            [
                'abTestingMode' => 1,
                'abContentVersion' => EmailInterface::CONTENT_VERSION_A,
                'result' => EmailInterface::CONTENT_VERSION_B
            ],
            [
                'abTestingMode' => 1,
                'abContentVersion' => EmailInterface::CONTENT_VERSION_B,
                'result' => EmailInterface::CONTENT_VERSION_A
            ],
        ];
    }
}
