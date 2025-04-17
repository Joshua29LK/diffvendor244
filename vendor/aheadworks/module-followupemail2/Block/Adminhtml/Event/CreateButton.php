<?php
namespace Aheadworks\Followupemail2\Block\Adminhtml\Event;

use Aheadworks\Followupemail2\Model\Event\TypePool as EventTypePool;
use Aheadworks\Followupemail2\Model\Event\TypeInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

/**
 * Class CreateButton
 * @package Aheadworks\Followupemail2\Block\Adminhtml\Event
 */
class CreateButton implements ButtonProviderInterface
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
     * @throws \Exception
     */
    public function getButtonData()
    {
        return [
            'label' => __('Create Event'),
            'class' => 'primary',
            'class_name' => Container::SPLIT_BUTTON,
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'aw_followupemail2_manage_events_form'
                                    . '.aw_followupemail2_manage_events_form.data.events',
                                'actionName' => 'onCreateEventButtonClick'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click' => '',
            'sort_order' => 10,
            'options' => $this->getOptions()
        ];
    }

    /**
     * Get button options
     *
     * @return array
     * @throws \Exception
     */
    private function getOptions()
    {
        $options = [];
        /** @var TypeInterface[] $eventTypes */
        $eventTypes = $this->eventTypePool->getAllEnabledTypes();
        foreach ($eventTypes as $eventType) {
            $options[] = [
                'id_hard' => $eventType->getCode(),
                'label' => __($eventType->getTitle()),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'aw_followupemail2_manage_events_form'
                                        . '.aw_followupemail2_manage_events_form.data.events',
                                    'actionName' => 'createEventForm',
                                    'params' => [
                                        true,
                                        [
                                            'event_type' => $eventType->getCode()
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }
        return $options;
    }
}
