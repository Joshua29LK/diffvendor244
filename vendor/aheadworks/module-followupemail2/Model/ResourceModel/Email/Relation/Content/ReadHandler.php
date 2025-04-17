<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Email\Relation\Content;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\EmailContentSearchResultsInterface;
use Aheadworks\Followupemail2\Api\EmailContentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Email\Relation\Content
 * @codeCoverageIgnore
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var EmailContentInterfaceFactory
     */
    private $emailContentFactory;

    /**
     * @var EmailContentRepositoryInterface
     */
    private $emailContentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param EmailContentInterfaceFactory $emailContentFactory
     * @param EmailContentRepositoryInterface $emailContentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        EmailContentInterfaceFactory $emailContentFactory,
        EmailContentRepositoryInterface $emailContentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->emailContentFactory = $emailContentFactory;
        $this->emailContentRepository = $emailContentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $this->searchCriteriaBuilder->addFilter(
                EmailContentInterface::EMAIL_ID,
                $entityId,
                'eq'
            );

            /** @var EmailContentSearchResultsInterface $result */
            $result = $this->emailContentRepository
                ->getList($this->searchCriteriaBuilder->create());
            /** @var EmailContentInterface[] $emailContentItems */
            $emailContentItems = $result->getItems();

            $entity->setContent($emailContentItems);
        }
        return $entity;
    }
}
