<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Queue;

use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Send
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Queue
 */
class Send extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::mail_log_actions';

    /**
     * @var QueueManagementInterface
     */
    private $queueManagement;

    /**
     * @param Context $context
     * @param QueueManagementInterface $queueManagement
     */
    public function __construct(
        Context $context,
        QueueManagementInterface $queueManagement
    ) {
        parent::__construct($context);
        $this->queueManagement = $queueManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $queueId = (int)$this->getRequest()->getParam('id');
        if ($queueId) {
            try {
                $result = $this->queueManagement->sendById($queueId);
                if ($result) {
                    $this->messageManager->addSuccessMessage(__('Email was successfully sent.'));
                } else {
                    $this->messageManager->addErrorMessage(__('This email can not be sent.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while sending the email.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
