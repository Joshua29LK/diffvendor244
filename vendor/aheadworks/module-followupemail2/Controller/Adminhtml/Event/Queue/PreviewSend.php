<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Api\EventQueueManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class PreviewSend
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue
 */
class PreviewSend extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::event_queue_actions';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * EventQueueManagementInterface
     */
    private $eventQueueManagement;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EventQueueManagementInterface $eventQueueManagement
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventQueueManagementInterface $eventQueueManagement
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventQueueManagement = $eventQueueManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $result = [
            'error'     => true,
            'message'   => __('Unknown error occurred!')
        ];

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->eventQueueManagement->sendNextScheduledEmail($id);

                $result = [
                    'error'     => false,
                    'message'   => __('Success.'),
                    'redirect_url'     => $this->getUrl(
                        'aw_followupemail2/event_queue/index/',
                        [
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    )
                ];
                $this->messageManager->addSuccessMessage(__('Email was successfully sent.'));
            } catch (\Exception $e) {
                $result = [
                    'error'     => true,
                    'message'   => __($e->getMessage())
                ];
            }
        }
        return $resultJson->setData($result);
    }
}
