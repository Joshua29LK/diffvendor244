<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Email\Relation\When;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SaveHandler
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Email\Relation\When
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param EventRepositoryInterface $eventRepository
     * @param EventTypePool $eventTypePool
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        EventRepositoryInterface $eventRepository,
        EventTypePool $eventTypePool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->eventRepository = $eventRepository;
        $this->eventTypePool = $eventTypePool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var EmailInterface $entity */
        if ($this->isPredictionEnabled($entity)) {
            if ($this->isWillBeFirstInChain($entity)) {
                $this->resetHoursAndMinutesForEmail($entity);
                $this->resetPredictionForEnabledEmails($entity);
            } elseif ($entity->getStatus() == EmailInterface::STATUS_ENABLED) {
                $this->resetPredictionForEmail($entity);
            }
        }

        return $entity;
    }

    /**
     * Check if the prediction is enabled for the email event
     *
     * @param EmailInterface $email
     * @return bool
     * @throws \Exception
     */
    private function isPredictionEnabled($email)
    {
        /** @var EventTypeInterface $eventType */
        $eventType = $this->getEventTypeInstance($email);
        if ($eventType) {
            return $eventType->isEmailPredictionEnabled();
        }
        return false;
    }

    /**
     * Retrieve event type instance
     *
     * @param EmailInterface $email
     * @return EventTypeInterface|false
     * @throws \Exception
     */
    private function getEventTypeInstance($email)
    {
        try {
            /** @var EventInterface $emailDataObject */
            $event = $this->eventRepository->get($email->getEventId());
            $eventTypeCode = $event->getEventType();

            /** @var EventTypeInterface $eventType */
            $eventType = $this->eventTypePool->getType($eventTypeCode);
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return $eventType;
    }

    /**
     * Check if the email will be first in the email chain
     *
     * @param EmailInterface $email
     * @return bool
     * @throws \Exception
     */
    private function isWillBeFirstInChain($email)
    {
        if ($email->getStatus() == EmailInterface::STATUS_ENABLED) {
            $allEnabledEmails = $this->getAllEnabledEmails($email->getEventId());
            $firstEnabledEmail = reset($allEnabledEmails);
            if ($email->getPosition() <= $firstEnabledEmail[EmailInterface::POSITION]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reset hours and minutes for email
     *
     * @param EmailInterface $email
     * @return void
     * @throws \Exception
     */
    private function resetHoursAndMinutesForEmail($email)
    {
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_fue2_event_email');
        $select = $connection->select()
            ->from($tableName, ['*'])
            ->where(EmailInterface::ID . ' = :id');

        $eventEmail = $connection->fetchRow($select, ['id' => $email->getId()]);

        $eventEmail[EmailInterface::EMAIL_SEND_HOURS] = 0;
        $eventEmail[EmailInterface::EMAIL_SEND_MINUTES] = 0;

        $connection->update($tableName, $eventEmail, ['id = ?' => (int)$eventEmail[EmailInterface::ID]]);
    }

    /**
     * Get all event emails
     *
     * @param int $eventId
     * @return array
     * @throws \Exception
     */
    private function getAllEnabledEmails($eventId)
    {
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_fue2_event_email');
        $select = $connection->select()
            ->from($tableName, ['*'])
            ->where(EmailInterface::EVENT_ID . ' = :event_id')
            ->where(EmailInterface::STATUS . ' = :status')
            ->order('position ASC');

        return $connection->fetchAll($select, ['event_id' => $eventId, 'status' => EmailInterface::STATUS_ENABLED]);
    }

    /**
     * Reset prediction for all event emails
     *
     * @param EmailInterface $email
     * @return void
     * @throws \Exception
     */
    private function resetPredictionForEnabledEmails($email)
    {
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_fue2_event_email');
        $select = $connection->select()
            ->from($tableName, ['*'])
            ->where(EmailInterface::EVENT_ID . ' = :event_id')
            ->order('position ASC');

        $allEventEmails = $connection->fetchAll($select, ['event_id' => $email->getEventId()]);

        foreach ($allEventEmails as $eventEmail) {
            if ($eventEmail[EmailInterface::ID] != $email->getId()
                && $eventEmail[EmailInterface::STATUS] == EmailInterface::STATUS_ENABLED
            ) {
                $eventEmail[EmailInterface::WHEN] = EmailInterface::WHEN_AFTER;
            }
            $connection->update($tableName, $eventEmail, ['id = ?' => (int)$eventEmail[EmailInterface::ID]]);
        }
    }

    /**
     * Reset prediction for the email
     *
     * @param EmailInterface $email
     * @return void
     * @throws \Exception
     */
    private function resetPredictionForEmail($email)
    {
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_fue2_event_email');
        $select = $connection->select()
            ->from($tableName, ['*'])
            ->where(EmailInterface::ID . ' = :id');

        $eventEmail = $connection->fetchRow($select, ['id' => $email->getId()]);
        $eventEmail[EmailInterface::WHEN] = EmailInterface::WHEN_AFTER;
        $connection->update($tableName, $eventEmail, ['id = ?' => (int)$eventEmail[EmailInterface::ID]]);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(EmailInterface::class)->getEntityConnectionName()
        );
    }
}
