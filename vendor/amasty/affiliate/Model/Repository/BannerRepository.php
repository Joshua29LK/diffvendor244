<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Api\BannerRepositoryInterface;
use Amasty\Affiliate\Api\Data;
use Amasty\Affiliate\Api\Data\BannerInterfaceFactory;
use Amasty\Affiliate\Model\ResourceModel\Banner;
use Amasty\Affiliate\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class BannerRepository extends AbstractRepository implements BannerRepositoryInterface
{

    /**
     * @var Banner
     */
    private $resource;

    /**
     * @var BannerInterfaceFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @var AttributeSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var BannerCollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Banner $resource,
        BannerInterfaceFactory $factory,
        AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        BannerCollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Data\BannerInterface $entity
     * @return Data\BannerInterface|\Amasty\Affiliate\Model\Banner
     * @throws CouldNotSaveException
     */
    public function save(Data\BannerInterface $entity)
    {
        if ($entity->getBannerId()) {
            /** @var \Amasty\Affiliate\Model\Banner $entity */
            $entity = $this->get($entity->getBannerId())->addData($entity->getData());
        }

        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getBannerId()]);
        } catch (\Exception $e) {
            if ($entity->getBannerId()) {
                throw new CouldNotSaveException(
                    __('Unable to save banner with ID %1. Error: %2', [$entity->getBannerId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new banner. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * @param int $id
     * @return Data\BannerInterface|\Amasty\Affiliate\Model\Banner
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Banner $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getBannerId()) {
                throw new NoSuchEntityException(__('Banner with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * @param Data\BannerInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\BannerInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getBannerId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove banner with ID %1. Error: %2', [$entity->getBannerId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove banner. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
