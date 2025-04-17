<?php
namespace Aheadworks\Followupemail2\Model\Source\Event;

use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Campaigns
 * @package Aheadworks\Followupemail2\Model\Source
 */
class Types implements OptionSourceInterface
{
    /**
     * @var EventTypePool
     */
    private $eventTypePool;

    /**
     * @param EventTypePool $eventTypePool
     */
    public function __construct(
        EventTypePool $eventTypePool
    ) {
        $this->eventTypePool = $eventTypePool;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /** @var EventTypeInterface[] $eventTypes */
        $eventTypes = $this->eventTypePool->getAllEnabledTypes();

        $typesOptions = [];
        foreach ($eventTypes as $eventCode => $eventType) {
            $typesOptions[] = [
                'value' => $eventCode,
                'label' => $eventType->getTitle(),
            ];
        }
        return $typesOptions;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $optionsArray = $this->toOptionArray();
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return string|null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
