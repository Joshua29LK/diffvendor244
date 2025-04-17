<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Email\Relation\Content;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\EmailContentRepositoryInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Email\Relation\Content
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var EmailContentRepositoryInterface
     */
    private $emailContentRepository;

    /**
     * @param EmailContentRepositoryInterface $emailContentRepository
     */
    public function __construct(
        EmailContentRepositoryInterface $emailContentRepository
    ) {
        $this->emailContentRepository = $emailContentRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();
        $contentObjects = $entity->getContent();

        /** @var EmailContentInterface $contentObject */
        foreach ($contentObjects as $contentObject) {
            $contentObject->setEmailId($entityId);
            $this->emailContentRepository->save($contentObject);
        }
        return $entity;
    }
}
