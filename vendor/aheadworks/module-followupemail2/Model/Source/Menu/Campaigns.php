<?php
namespace Aheadworks\Followupemail2\Model\Source\Menu;

use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\Collection as CampaignCollection;
use Magento\Framework\Data\CollectionDataSourceInterface;

/**
 * Class Campaigns
 * @package Aheadworks\Followupemail2\Model\Source\Menu
 */
class Campaigns implements CollectionDataSourceInterface
{
    /**
     * @var CampaignCollectionFactory
     */
    private $campaignCollectionFactory;

    /**
     * @param CampaignCollectionFactory $campaignCollectionFactory
     */
    public function __construct(
        CampaignCollectionFactory $campaignCollectionFactory
    ) {
        $this->campaignCollectionFactory = $campaignCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /** @var CampaignCollection $collection */
        $collection = $this->campaignCollectionFactory->create();
        $collection->addFilterByStatus(CampaignInterface::STATUS_ENABLED);

        $campaignOptions = [];
        foreach ($collection as $item) {
            $campaignOptions[] = [
                'value' => $item->getId(),
                'label' => $item->getName(),
            ];
        }
        return $campaignOptions;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $optionsArray = $this->toOptionArray();
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return string|null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
