<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model\Source\Menu;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Model\Source\Menu\Campaigns;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\Collection as CampaignCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Followupemail2\Model\Source\Menu\Campaigns
 */
class CampaignsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Campaigns
     */
    private $model;

    /**
     * @var CampaignCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $campaignCollectionFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);

        $this->campaignCollectionFactoryMock = $this->getMockBuilder(CampaignCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Campaigns::class,
            [
                'campaignCollectionFactory' => $this->campaignCollectionFactoryMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $campaignId = 1;
        $campaignName = 'Test campaign';
        $result = [
            ['value' => $campaignId, 'label' => $campaignName,]
        ];

        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getId')
            ->willReturn($campaignId);
        $campaignMock->expects($this->once())
            ->method('getName')
            ->willReturn($campaignName);
        $collectionMock = $this->getMockBuilder(CampaignCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->campaignCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFilterByStatus')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$campaignMock]));

        $this->assertSame($result, $this->model->toOptionArray());
    }

    /**
     * Test getOptions method
     */
    public function testGetOptions()
    {
        $campaignId = 1;
        $campaignName = 'Test campaign';
        $result = [
            $campaignId => $campaignName,
        ];

        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getId')
            ->willReturn($campaignId);
        $campaignMock->expects($this->once())
            ->method('getName')
            ->willReturn($campaignName);
        $collectionMock = $this->getMockBuilder(CampaignCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->campaignCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFilterByStatus')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$campaignMock]));

        $this->assertSame($result, $this->model->getOptions());
    }

    /**
     * Test getOptionByValue method
     */
    public function testGetOptionByValue()
    {
        $campaignId = 1;
        $campaignName = 'Test campaign';

        $campaignMock = $this->getMockBuilder(CampaignInterface::class)
            ->getMockForAbstractClass();
        $campaignMock->expects($this->once())
            ->method('getId')
            ->willReturn($campaignId);
        $campaignMock->expects($this->once())
            ->method('getName')
            ->willReturn($campaignName);
        $collectionMock = $this->getMockBuilder(CampaignCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->campaignCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('addFilterByStatus')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$campaignMock]));

        $this->assertSame($campaignName, $this->model->getOptionByValue($campaignId));
    }
}
