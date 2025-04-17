<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Api\Data;
use Amasty\Affiliate\Api\Data\LinksInterfaceFactory;
use Amasty\Affiliate\Model\ResourceModel\Links as LinkResource;
use Amasty\Affiliate\Model\ResourceModel\Links\CollectionFactory;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class LinksRepository extends AbstractRepository implements \Amasty\Affiliate\Api\LinksRepositoryInterface
{

    /**
     * @var LinkResource
     */
    private $resource;

    /**
     * @var LinksInterfaceFactory
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
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        LinkResource $resource,
        LinksInterfaceFactory $factory,
        AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Data\LinksInterface $entity
     * @return Data\LinksInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\LinksInterface $entity)
    {
        if ($entity->getLinkId()) {
            $entity = $this->get($entity->getLinkId())->addData($entity->getData());
        }

        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getLinkId()]);
        } catch (\Exception $e) {
            if ($entity->getLinkId()) {
                throw new CouldNotSaveException(
                    __('Unable to save links with ID %1. Error: %2', [$entity->getLinkId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new links. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * @param int $id
     * @return Data\LinksInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Links $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getLinkId()) {
                throw new NoSuchEntityException(__('Links with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * @param Data\LinksInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\LinksInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getLinkId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove links with ID %1. Error: %2', [$entity->getLinkId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove links. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
