<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassSend
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Queue
 */
class MassSend extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::mail_log_actions';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var QueueCollectionFactory
     */
    private $queueCollectionFactory;

    /**
     * @var QueueManagementInterface
     */
    private $queueManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param QueueManagementInterface $queueManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        QueueCollectionFactory $queueCollectionFactory,
        QueueManagementInterface $queueManagement
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->queueManagement = $queueManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->queueCollectionFactory->create());
            $count = 0;
            foreach ($collection->getAllIds() as $queueId) {
                $result = $this->queueManagement->sendById($queueId);
                if ($result) {
                    $count++;
                }
            }
            if ($count > 0) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 email(s) have been sent.', $count)
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('None of selected emails can be sent.')
                );
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while sending the email(s).')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
