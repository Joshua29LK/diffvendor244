<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Api\Data;
use Amasty\Affiliate\Api\LifetimeRepositoryInterface;
use Amasty\Affiliate\Api\Data\LifetimeInterfaceFactory;
use Amasty\Affiliate\Model\ResourceModel\Lifetime;
use Amasty\Affiliate\Model\ResourceModel\Lifetime\CollectionFactory;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class LifetimeRepository extends AbstractRepository implements LifetimeRepositoryInterface
{

    /**
     * @var Lifetime
     */
    private $resource;

    /**
     * @var LifetimeInterfaceFactory
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
        Lifetime $resource,
        LifetimeInterfaceFactory $factory,
        AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Data\LifetimeInterface $entity
     * @return Data\LifetimeInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\LifetimeInterface $entity)
    {
        if ($entity->getLifetimeId()) {
            $entity = $this->get($entity->getLifetimeId())->addData($entity->getData());
        }

        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getLifetimeId()]);
        } catch (\Exception $e) {
            if ($entity->getLifetimeId()) {
                throw new CouldNotSaveException(
                    __('Unable to save lifetime with ID %1. Error: %2', [$entity->getLifetimeId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new lifetime. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * @param int $id
     * @return Data\LifetimeInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Lifetime $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getLifetimeId()) {
                throw new NoSuchEntityException(__('Lifetime with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * @param Data\LifetimeInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\LifetimeInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getLifetimeId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove lifetime with ID %1. Error: %2', [$entity->getLifetimeId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove lifetime. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
