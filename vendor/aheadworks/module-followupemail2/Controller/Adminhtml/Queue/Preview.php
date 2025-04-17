<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\QueueRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

/**
 * Class Preview
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Queue
 */
class Preview extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::mail_log_actions';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var QueueManagementInterface
     */
    private $queueManagement;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param QueueRepositoryInterface $queueRepository
     * @param QueueManagementInterface $queueManagement
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        QueueRepositoryInterface $queueRepository,
        QueueManagementInterface $queueManagement
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->queueRepository = $queueRepository;
        $this->queueManagement = $queueManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $queueId = (int)$this->getRequest()->getParam('id');
        if ($queueId) {
            try {
                /** @var QueueInterface $queue */
                $queue = $this->queueRepository->get($queueId);

                /** @var PreviewInterface $preview */
                $preview = $this->queueManagement->getPreview($queue);
                $this->coreRegistry->register('aw_followupemail2_preview', $preview);

                $this->_view->loadLayout(['aw_followupemail2_preview'], true, true, false);
                $this->_view->renderLayout();
                return;
            } catch (\Exception $e) {
            }
        }
        $this->_forward('noroute');
    }
}
