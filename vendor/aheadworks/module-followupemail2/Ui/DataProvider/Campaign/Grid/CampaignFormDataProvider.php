<?php
namespace Aheadworks\Followupemail2\Ui\DataProvider\Campaign\Grid;

use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\CollectionFactory;
use Aheadworks\Followupemail2\Model\ResourceModel\Campaign\Collection;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class CampaignFormDataProvider
 * @package Aheadworks\Followupemail2\Ui\DataProvider\Campaign\Grid
 * @codeCoverageIgnore
 */
class CampaignFormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $id = $this->request->getParam($this->getRequestFieldName());
        $duplicate = $this->request->getParam('duplicate');
        $data = [];
        if ($id) {
            /** @var \Aheadworks\Followupemail2\Model\Campaign $campaign */
            foreach ($this->getCollection()->getItems() as $campaign) {
                if ($id == $campaign->getId()) {
                    if ($duplicate) {
                        $campaignData = $campaign->getData();
                        unset($campaignData[CampaignInterface::ID]);
                        $campaignData[CampaignInterface::STATUS] = CampaignInterface::STATUS_DISABLED;
                        $campaignData[CampaignInterface::NAME] .= ' #1';
                        $campaignData['duplicate_id'] = $id;
                        $data[$id] = $campaignData;
                    } else {
                        $data[$id] = $campaign->getData();
                    }
                }
            }
        }
        return $data;
    }
}
