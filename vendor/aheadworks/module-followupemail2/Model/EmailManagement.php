<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailSearchResultsInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterfaceFactory;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Class EmailManagement
 * @package Aheadworks\Followupemail2\Model
 */
class EmailManagement implements EmailManagementInterface
{
    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var PreviewInterfaceFactory
     */
    private $previewFactory;

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param EmailRepositoryInterface $emailRepository
     * @param StatisticsManagementInterface $statisticsManagement
     * @param PreviewInterfaceFactory $previewFactory
     * @param Sender $sender
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        EmailRepositoryInterface $emailRepository,
        StatisticsManagementInterface $statisticsManagement,
        PreviewInterfaceFactory $previewFactory,
        Sender $sender,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->emailRepository = $emailRepository;
        $this->statisticsManagement = $statisticsManagement;
        $this->previewFactory = $previewFactory;
        $this->sender = $sender;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function disableEmail($emailId)
    {
        $email = $this->emailRepository->get($emailId);
        $email->setStatus(EmailInterface::STATUS_DISABLED);
        $email = $this->emailRepository->save($email);

        return $email;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailsByEventId($eventId, $enabledOnly = false)
    {
        $this->sortOrderBuilder
            ->setField(EmailInterface::POSITION)
            ->setAscendingDirection();

        $this->searchCriteriaBuilder
            ->addFilter(EmailInterface::EVENT_ID, $eventId, 'eq')
            ->addSortOrder($this->sortOrderBuilder->create());

        if ($enabledOnly) {
            $this->searchCriteriaBuilder
                ->addFilter(EmailInterface::STATUS, EmailInterface::STATUS_ENABLED, 'eq');
        }

        /** @var EmailSearchResultsInterface $emailsResult */
        $emailsResult = $this->emailRepository->getList($this->searchCriteriaBuilder->create());

        return $emailsResult->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getNextEmailToSend($eventId, $countOfSentEmails)
    {
        /** @var EmailInterface[] $eventEmails */
        $eventEmails = $this->getEmailsByEventId($eventId, true);
        $indexOfNextEmail = $countOfSentEmails;
        if (isset($eventEmails[$indexOfNextEmail])) {
            /** @var EmailInterface $email */
            return $eventEmails[$indexOfNextEmail];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function changeStatus($emailId)
    {
        /** @var EmailInterface $email */
        $email = $this->emailRepository->get($emailId);
        if ($email->getStatus() == EmailInterface::STATUS_DISABLED) {
            $email->setStatus(EmailInterface::STATUS_ENABLED);
        } else {
            $email->setStatus(EmailInterface::STATUS_DISABLED);
        }
        $email = $this->emailRepository->save($email);

        return $email;
    }

    /**
     * {@inheritdoc}
     */
    public function changePosition($emailId, $position)
    {
        /** @var EmailInterface $email */
        $email = $this->emailRepository->get($emailId);
        $email->setPosition($position);
        $email = $this->emailRepository->save($email);

        return $email;
    }

    /**
     * {@inheritdoc}
     */
    public function isFirst($emailId, $eventId)
    {
        $result = true;
        $this->sortOrderBuilder
            ->setField(EmailInterface::POSITION)
            ->setAscendingDirection();

        $this->searchCriteriaBuilder
            ->addFilter(EmailInterface::EVENT_ID, $eventId, 'eq')
            ->addFilter(EmailInterface::STATUS, EmailInterface::STATUS_ENABLED, 'eq')
            ->addSortOrder($this->sortOrderBuilder->create());

        /** @var EmailSearchResultsInterface $emailsResult */
        $emailsResult = $this->emailRepository->getList($this->searchCriteriaBuilder->create());

        if ($emailsResult->getTotalCount() > 0) {
            if (empty($emailId)) {
                $result = false;
            } else {
                $firstEmail = $emailsResult->getItems()[0];
                if ($firstEmail->getId() != $emailId) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isCanBeFirst($emailId, $eventId)
    {
        $result = true;

        $this->sortOrderBuilder
            ->setField(EmailInterface::POSITION)
            ->setAscendingDirection();

        $this->searchCriteriaBuilder
            ->addFilter(EmailInterface::EVENT_ID, $eventId, 'eq')
            ->addSortOrder($this->sortOrderBuilder->create());

        /** @var EmailSearchResultsInterface $emailsResult */
        $emailsResult = $this->emailRepository->getList($this->searchCriteriaBuilder->create());

        if ($emailsResult->getTotalCount() > 0) {
            $firstEmailCandidateIds = [];
            $enabledCandidateFound = false;
            /** @var EmailInterface $email */
            foreach ($emailsResult->getItems() as $email) {
                if ($email->getStatus() == EmailInterface::STATUS_ENABLED) {
                    $firstEmailCandidateIds[] = $email->getId();
                    $enabledCandidateFound = true;
                    break;
                }
                $firstEmailCandidateIds[] = $email->getId();
            }

            if ((empty($emailId) && $enabledCandidateFound)
                || ($emailId && !in_array($emailId, $firstEmailCandidateIds))
            ) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatistics($email)
    {
        return $this->statisticsManagement->getByEmailId($email->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getStatisticsByContentId($emailContentId)
    {
        return $this->statisticsManagement->getByEmailContentId($emailContentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewEmailPosition($eventId)
    {
        $this->sortOrderBuilder
            ->setField(EmailInterface::POSITION)
            ->setDescendingDirection();

        $this->searchCriteriaBuilder
            ->addFilter(EmailInterface::EVENT_ID, $eventId, 'eq')
            ->addSortOrder($this->sortOrderBuilder->create());

        /** @var EmailSearchResultsInterface $emailsResult */
        $emailsResult = $this->emailRepository->getList($this->searchCriteriaBuilder->create());

        if ($emailsResult->getTotalCount() > 0) {
            /** @var EmailInterface $email */
            $email = $emailsResult->getItems()[0];
            return $email->getPosition() + 1;
        }
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreview($storeId, $emailContent)
    {
        /** @var array $previewContent */
        $previewContent = $this->sender->getTestPreview($storeId, $emailContent);

        /** @var PreviewInterface $preview */
        $preview = $this->previewFactory->create();
        $preview
            ->setStoreId($storeId)
            ->setSenderName($emailContent->getSenderName())
            ->setSenderEmail($emailContent->getSenderEmail())
            ->setRecipientName($previewContent['recipient_name'])
            ->setRecipientEmail($previewContent['recipient_email'])
            ->setSubject($previewContent['subject'])
            ->setContent($previewContent['content']);

        return $preview;
    }
}
