<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CancelEvent
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue
 */
class CancelEvent extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::event_queue_actions';

    /**
     * @var EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @param Context $context
     * @param EventQueueManagementInterface $eventQueueManagement
     */
    public function __construct(
        Context $context,
        EventQueueManagementInterface $eventQueueManagement
    ) {
        parent::__construct($context);
        $this->eventQueueManagement = $eventQueueManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $eventQueueId = (int)$this->getRequest()->getParam('id');
        if ($eventQueueId) {
            try {
                $this->eventQueueManagement->cancelEvent($eventQueueId);
                $this->messageManager->addSuccessMessage(__('The email chain was successfully cancelled.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while cancelling the email.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
