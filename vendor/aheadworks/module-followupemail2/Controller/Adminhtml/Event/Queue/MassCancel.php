<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Indexer\ScheduledEmails\CollectionFactory
    as ScheduledEmailsCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassCancel
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue
 */
class MassCancel extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::event_queue_actions';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var ScheduledEmailsCollectionFactory
     */
    private $scheduledEmailsCollectionFactory;

    /**
     * @var EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ScheduledEmailsCollectionFactory $scheduledEmailsCollectionFactory
     * @param EventQueueManagementInterface $eventQueueManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ScheduledEmailsCollectionFactory $scheduledEmailsCollectionFactory,
        EventQueueManagementInterface $eventQueueManagement
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->scheduledEmailsCollectionFactory = $scheduledEmailsCollectionFactory;
        $this->eventQueueManagement = $eventQueueManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->scheduledEmailsCollectionFactory->create());
            $count = 0;
            foreach ($collection->getAllIds() as $eventQueueId) {
                if ($this->eventQueueManagement->cancelScheduledEmail($eventQueueId)) {
                    $count++;
                }
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while cancelling the email(s).')
            );
        }

        if ($count > 0) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 email(s) have been cancelled.', $count)
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('None of selected emails can be cancelled.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
