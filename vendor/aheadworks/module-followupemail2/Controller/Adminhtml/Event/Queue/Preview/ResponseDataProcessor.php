<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Preview;

use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Block\Adminhtml\Preview as PreviewBlock;
use Magento\Framework\View\LayoutFactory;

/**
 * Class ResponseDataProcessor
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue
 */
class ResponseDataProcessor
{
    /**
     * @var EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @param EventQueueManagementInterface $eventQueueManagement
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        EventQueueManagementInterface $eventQueueManagement,
        LayoutFactory $layoutFactory
    ) {
        $this->eventQueueManagement = $eventQueueManagement;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Get prepared data
     *
     * @param EventQueueInterface $eventQueueItem
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPreparedData($eventQueueItem)
    {
        /** @var PreviewInterface $preview */
        $preview = $this->eventQueueManagement->getScheduledEmailPreview($eventQueueItem);

        /** @var PreviewBlock $previewBlock */
        $previewBlock = $this->layoutFactory->create()
            ->createBlock(PreviewBlock::class);

        $renderedPreview = $previewBlock
            ->setPreview($preview)
            ->toHtml();

        return [
            'id' => $eventQueueItem->getId(),
            'preview' => $renderedPreview,
        ];
    }
}
