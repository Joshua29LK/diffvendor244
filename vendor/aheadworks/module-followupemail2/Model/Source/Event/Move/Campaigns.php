<?php
namespace Aheadworks\Followupemail2\Model\Source\Event\Move;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Campaigns
 * @package Aheadworks\Followupemail2\Model\Source\Menu
 */
class Campaigns implements OptionSourceInterface
{
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /** @var CampaignInterface[] $campaigns */
        $campaigns = $this->campaignRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        $campaignOptions = [];
        foreach ($campaigns as $item) {
            $campaignOptions[] = [
                'value' => $item->getId(),
                'label' => $this->getName($item),
            ];
        }
        return $campaignOptions;
    }

    /**
     * Get campaign name
     *
     * @param CampaignInterface $campaign
     * @return string
     */
    private function getName($campaign)
    {
        $campaignName = $campaign->getName();
        if ($campaign->getStatus() == CampaignInterface::STATUS_DISABLED) {
            $campaignName = __('%1 (inactive)', $campaignName);
        }
        return $campaignName;
    }
}
