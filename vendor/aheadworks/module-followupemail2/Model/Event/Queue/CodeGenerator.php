<?php
namespace Aheadworks\Followupemail2\Model\Event\Queue;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\Collection as EventQueueCollection;
use Aheadworks\Followupemail2\Model\ResourceModel\Event\Queue\CollectionFactory as EventQueueCollectionFactory;
use Magento\Framework\Math\Random;

/**
 * Class CodeGenerator
 * @package Aheadworks\Followupemail2\Model\Event\Queue
 */
class CodeGenerator
{
    /**
     * Unsubscribe code length
     */
    const CODE_LENGTH = 32;

    /**
     * @var EventQueueCollectionFactory
     */
    private $eventQueueCollectionFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param EventQueueCollectionFactory $eventQueueCollectionFactory
     * @param Random $random
     */
    public function __construct(
        EventQueueCollectionFactory $eventQueueCollectionFactory,
        Random $random
    ) {
        $this->eventQueueCollectionFactory = $eventQueueCollectionFactory;
        $this->random = $random;
    }

    /**
     * Get unsubscribe code
     *
     * @return string
     */
    public function getCode()
    {
        do {
            $securityCode = $this->random->getRandomString(self::CODE_LENGTH);

            /** @var EventQueueCollection $collection */
            $collection = $this->eventQueueCollectionFactory->create();
            $collection->addFilter(EventQueueInterface::SECURITY_CODE, $securityCode, 'eq');
        } while ($collection->getSize() > 0);

        return $securityCode;
    }
}
