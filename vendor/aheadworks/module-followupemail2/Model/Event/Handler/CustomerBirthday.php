<?php
namespace Aheadworks\Followupemail2\Model\Event\Handler;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventHistoryInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventHistoryRepositoryInterface;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Aheadworks\Followupemail2\Model\Event\Validator as EventValidator;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;

/**
 * Class CustomerBirthday
 * @package Aheadworks\Followupemail2\Model\Event\Handler
 */
class CustomerBirthday extends AbstractHandler
{
    /**
     * @var string
     */
    protected $type = EventInterface::TYPE_CUSTOMER_BIRTHDAY;

    /**
     * @var string
     */
    protected $referenceDataKey = 'entity_id';

    /**
     * @var string
     */
    protected $eventObjectVariableName = 'customer';

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var EventHistoryInterfaceFactory
     */
    private $eventHistoryFactory;

    /**
     * @param CampaignManagementInterface $campaignManagement
     * @param EventRepositoryInterface $eventRepository
     * @param EventHistoryRepositoryInterface $eventHistoryRepository
     * @param EventValidator $eventValidator
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Serializer $serializer
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param EmailManagementInterface $emailManagement
     * @param EventHistoryInterfaceFactory $eventHistoryFactory
     */
    public function __construct(
        CampaignManagementInterface $campaignManagement,
        EventRepositoryInterface $eventRepository,
        EventHistoryRepositoryInterface $eventHistoryRepository,
        EventValidator $eventValidator,
        EventQueueManagementInterface $eventQueueManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        CustomerCollectionFactory $customerCollectionFactory,
        EmailManagementInterface $emailManagement,
        EventHistoryInterfaceFactory $eventHistoryFactory
    ) {
        parent::__construct(
            $campaignManagement,
            $eventRepository,
            $eventHistoryRepository,
            $eventValidator,
            $eventQueueManagement,
            $searchCriteriaBuilder,
            $serializer
        );
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->emailManagement = $emailManagement;
        $this->eventHistoryFactory = $eventHistoryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(EventHistoryInterface $eventHistoryItem)
    {
        /** @var EventInterface[] $birthdayEvents */
        $birthdayEvents = $this->getEventsForValidation(EventInterface::TYPE_CUSTOMER_BIRTHDAY);

        foreach ($birthdayEvents as $event) {
            $birthdayDate = $this->getBirthdayDate($event->getId());
            if ($birthdayDate) {
                foreach ($this->getCustomersByBirthdayDate($birthdayDate) as $customer) {
                    $eventData = $this->getEventData($customer);

                    if ($this->eventValidator->validate($event, $eventData, $customer)) {
                        $this->eventQueueManagement->cancelEventsByEventId($event->getId(), $customer->getId());

                        /** @var EventHistoryInterface $eventHistoryItem */
                        $eventHistoryItem = $this->eventHistoryFactory->create();
                        $eventHistoryItem
                            ->setReferenceId($customer->getId())
                            ->setEventType(EventInterface::TYPE_CUSTOMER_BIRTHDAY)
                            ->setEventData($this->serializer->serialize($eventData));

                        $this->eventQueueManagement->add($event, $eventHistoryItem);
                    }
                }
            }
        }
    }

    /**
     * Get the birthday date
     *
     * @param int $eventId
     * @return null|string
     */
    private function getBirthdayDate($eventId)
    {
        $birthdayDate = null;

        /** @var EmailInterface[] $emails */
        $emails = $this->emailManagement->getEmailsByEventId($eventId, true);

        if (count($emails) > 0) {
            $firstEmail = reset($emails);
            if ($firstEmail->getWhen() == EmailInterface::WHEN_BEFORE) {
                $birthdayDate = $this->getFormattedDate($firstEmail->getEmailSendDays());
            } else {
                $birthdayDate = $this->getFormattedDate();
            }
        }

        return $birthdayDate;
    }

    /**
     * Get formatted date
     *
     * @param int $daysOffset
     * @return string
     */
    private function getFormattedDate($daysOffset = 0)
    {
        $resultDate = date(
            'm-d',
            strtotime("+" . $daysOffset . " days")
        );
        return $resultDate;
    }

    /**
     * Get customers by the birthday date
     *
     * @param string $birthdayDate
     * @return Customer[]|CustomerInterface[]
     */
    private function getCustomersByBirthdayDate($birthdayDate)
    {
        /** @var CustomerCollection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->addBirthdayFilter($birthdayDate);

        return $collection->getItems();
    }

    /**
     *  Get event data
     *
     * @param Customer $customer
     * @return array
     */
    private function getEventData($customer)
    {
        $customerData = array_merge($customer->getData(), [
            'customer_name' => $customer->getName(),
            'customer_group_id' => $customer->getGroupId()
        ]);
        unset($customerData['password_hash']);

        return $customerData;
    }

    /**
     * Get event object
     *
     * @param array $eventData
     * @return Customer|null
     */
    public function getEventObject($eventData)
    {
        /** @var CustomerCollection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->addFilter($this->getReferenceDataKey(), $eventData[$this->getReferenceDataKey()]);
        $customer = $collection->getFirstItem();

        if (!$customer->getId()) {
            return null;
        }

        return $customer;
    }
}
