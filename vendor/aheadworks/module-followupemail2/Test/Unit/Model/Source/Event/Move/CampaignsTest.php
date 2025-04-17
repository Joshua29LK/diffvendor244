<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Event;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignSearchResultsInterface;
use Aheadworks\Followupemail2\Model\Source\Event\Move\Campaigns;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Event\Move\CampaignsTest
 */
class CampaignsTest extends TestCase
{
    /**
     * @var Campaigns
     */
    private $model;
    /**
     * @var CampaignRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->campaignRepositoryMock = $this->getMockBuilder(CampaignRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Campaigns::class,
            [
                'campaignRepository' => $this->campaignRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $enabledCampaignId = 1;
        $enabledCampaignName = 'Campaign 1';
        $enabledCampaign = $this->getCampaignMock(
            $enabledCampaignId,
            $enabledCampaignName,
            CampaignInterface::STATUS_ENABLED
        );
        $disabledCampaignId = 2;
        $disabledCampaignName = 'Campaign 2';
        $disabledCampaign = $this->getCampaignMock(
            $disabledCampaignId,
            $disabledCampaignName,
            CampaignInterface::STATUS_DISABLED
        );
        $campaigns = [$enabledCampaign, $disabledCampaign];
        $result = [
            ['value' => $enabledCampaignId, 'label' => $enabledCampaignName],
            ['value' => $disabledCampaignId, 'label' => __('%1 (inactive)', $disabledCampaignName) ],
        ];

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $campaignSearchResultMock = $this->getMockBuilder(CampaignSearchResultsInterface::class)
            ->getMockForAbstractClass();
        $campaignSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn($campaigns);
        $this->campaignRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($campaignSearchResultMock);

        $this->assertEquals($result, $this->model->toOptionArray());
    }

    /**
     * Get campaign mock
     *
     * @param int $id
     * @param string $name
     * @param int $status
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCampaignMock($id, $name, $status)
    {
        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $campaignMock->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $campaignMock->expects($this->any())
            ->method('getStatus')
            ->willReturn($status);

        return $campaignMock;
    }
}
