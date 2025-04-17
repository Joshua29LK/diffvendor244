<?php
namespace Aheadworks\Followupemail2\Model\Unsubscribe;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterfaceFactory;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeSearchResultsInterface;
use Aheadworks\Followupemail2\Api\UnsubscribeRepositoryInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class Service
{
    /**
     * @var UnsubscribeInterfaceFactory
     */
    private $unsubscribeFactory;

    /**
     * @var UnsubscribeRepositoryInterface
     */
    private $unsubscribeRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param UnsubscribeInterfaceFactory $unsubscribeFactory
     * @param UnsubscribeRepositoryInterface $unsubscribeRepository
     * @param EventRepositoryInterface $eventRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        UnsubscribeInterfaceFactory $unsubscribeFactory,
        UnsubscribeRepositoryInterface $unsubscribeRepository,
        EventRepositoryInterface $eventRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->unsubscribeFactory = $unsubscribeFactory;
        $this->unsubscribeRepository = $unsubscribeRepository;
        $this->eventRepository = $eventRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Unsubscribe from event
     *
     * @param int $eventId
     * @param string $email
     * @param int $storeId
     * @return bool
     */
    public function unsubscribeFromEvent($eventId, $email, $storeId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_ID, 'eq')
            ->addFilter(UnsubscribeInterface::VALUE, $eventId, 'eq')
            ->addFilter(UnsubscribeInterface::EMAIL, $email, 'eq')
            ->addFilter(UnsubscribeInterface::STORE_ID, $storeId, 'eq');

        /** @var UnsubscribeSearchResultsInterface $result */
        $result = $this->unsubscribeRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        if ($result->getTotalCount() == 0) {
            try {
                /** @var UnsubscribeInterface $unsubscribeItem */
                $unsubscribeItem = $this->unsubscribeFactory->create();
                $unsubscribeItem
                    ->setType(UnsubscribeInterface::TYPE_EVENT_ID)
                    ->setValue($eventId)
                    ->setEmail($email)
                    ->setStoreId($storeId);
                $this->unsubscribeRepository->save($unsubscribeItem);
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Unsubscribe from event type
     *
     * @param string $eventType
     * @param string $email
     * @param int $storeId
     * @return bool
     */
    public function unsubscribeFromEventType($eventType, $email, $storeId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_EVENT_TYPE, 'eq')
            ->addFilter(UnsubscribeInterface::VALUE, $eventType, 'eq')
            ->addFilter(UnsubscribeInterface::EMAIL, $email, 'eq')
            ->addFilter(UnsubscribeInterface::STORE_ID, $storeId, 'eq');

        /** @var UnsubscribeSearchResultsInterface $result */
        $result = $this->unsubscribeRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        if ($result->getTotalCount() == 0) {
            try {
                /** @var UnsubscribeInterface $unsubscribeItem */
                $unsubscribeItem = $this->unsubscribeFactory->create();
                $unsubscribeItem
                    ->setType(UnsubscribeInterface::TYPE_EVENT_TYPE)
                    ->setValue($eventType)
                    ->setEmail($email)
                    ->setStoreId($storeId);
                $this->unsubscribeRepository->save($unsubscribeItem);
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Unsubscribe from all events
     *
     * @param string $email
     * @param int $storeId
     * @return bool
     */
    public function unsubscribeFromAll($email, $storeId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(UnsubscribeInterface::TYPE, UnsubscribeInterface::TYPE_ALL, 'eq')
            ->addFilter(UnsubscribeInterface::EMAIL, $email, 'eq')
            ->addFilter(UnsubscribeInterface::STORE_ID, $storeId, 'eq');

        /** @var UnsubscribeSearchResultsInterface $result */
        $result = $this->unsubscribeRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        if ($result->getTotalCount() == 0) {
            try {
                /** @var UnsubscribeInterface $unsubscribeItem */
                $unsubscribeItem = $this->unsubscribeFactory->create();
                $unsubscribeItem
                    ->setType(UnsubscribeInterface::TYPE_ALL)
                    ->setValue(null)
                    ->setEmail($email)
                    ->setStoreId($storeId);
                $this->unsubscribeRepository->save($unsubscribeItem);
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * If a customer with the email is unsubscribed to the event specified
     *
     * @param int $eventId
     * @param string $email
     * @param int $storeId
     * @return bool
     */
    public function isUnsubscribed($eventId, $email, $storeId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(UnsubscribeInterface::EMAIL, $email, 'eq')
            ->addFilter(UnsubscribeInterface::STORE_ID, $storeId, 'eq');

        /** @var UnsubscribeSearchResultsInterface $result */
        $result = $this->unsubscribeRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        foreach ($result->getItems() as $unsubscribeItem) {
            switch ($unsubscribeItem->getType()) {
                case UnsubscribeInterface::TYPE_ALL:
                    return true;

                case UnsubscribeInterface::TYPE_EVENT_TYPE:
                    try {
                        /** @var EventInterface $event */
                        $event = $this->eventRepository->get($eventId);
                        if ($event->getEventType() == $unsubscribeItem->getValue()) {
                            return true;
                        }
                    } catch (NoSuchEntityException $e) {
                    }
                    break;

                case UnsubscribeInterface::TYPE_EVENT_ID:
                    if ($unsubscribeItem->getValue() == $eventId) {
                        return true;
                    }
                    break;

                default:
            }
        }
        return false;
    }
}
