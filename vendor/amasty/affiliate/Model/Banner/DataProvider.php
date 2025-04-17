<?php

namespace Amasty\Affiliate\Model\Banner;

use Amasty\Affiliate\Model\ResourceModel\Banner\CollectionFactory;
use Amasty\Affiliate\Model\Banner;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\Affiliate\Model\RegistryConstants;

/**
 * Class DataProvider
 * @package Amasty\Affiliate\Model\Banner
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
     /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $currentBanner = $this->coreRegistry->registry(RegistryConstants::CURRENT_AFFILIATE_BANNER);
        $data = $currentBanner->getData();
        if (!empty($data)) {
            if (isset($data['image'])) {
                unset($data['image']);
                $data['image'][0]['name'] = $currentBanner->getData('image');
                $data['image'][0]['url'] = $currentBanner->getImageUrl();
            }
            $banner = $this->collection->getNewEmptyItem();
            $banner->setData($data);
            $this->loadedData[$banner->getId()] = $banner->getData();
        }

        return $this->loadedData;
    }
}
