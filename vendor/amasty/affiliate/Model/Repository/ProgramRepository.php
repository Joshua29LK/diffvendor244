<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Api\Data;
use Amasty\Affiliate\Api\Data\ProgramCommissionCalculationInterface;
use Amasty\Affiliate\Api\Data\ProgramInterfaceFactory;
use Amasty\Affiliate\Model\ProgramCommissionCalculation;
use Amasty\Affiliate\Model\ResourceModel\Program;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Amasty\Affiliate\Model\ResourceModel\Program\ProgramCommissionCalculation\CollectionFactory
    as CommissionCalculationCollectionFactory;
use Amasty\Affiliate\Model\ResourceModel\ProgramCommissionCalculation as ProgramCommissionCalculationResource;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class ProgramRepository extends AbstractRepository implements \Amasty\Affiliate\Api\ProgramRepositoryInterface
{
    /**
     * @var Program
     */
    private $resource;

    /**
     * @var Program
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
        Program $resource,
        ProgramInterfaceFactory $factory,
        AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Data\ProgramInterface $entity
     *
     * @return Data\ProgramInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\ProgramInterface $entity)
    {
        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getProgramId()]);
        } catch (\Exception $e) {
            if ($entity->getProgramId()) {
                throw new CouldNotSaveException(
                    __('Unable to save program with ID %1. Error: %2', [$entity->getProgramId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new program. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * @param int $id
     *
     * @return Data\ProgramInterface|\Amasty\Affiliate\Model\Program|mixed
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Program $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getProgramId()) {
                throw new NoSuchEntityException(__('Program with specified ID "%1" not found.', $id));
            }

            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * @param Data\ProgramInterface $entity
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ProgramInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getProgramId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove program with ID %1. Error: %2', [$entity->getProgramId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove program. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
