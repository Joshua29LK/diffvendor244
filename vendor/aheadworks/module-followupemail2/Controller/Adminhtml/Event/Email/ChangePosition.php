<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class ChangePosition
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email
 */
class ChangePosition extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Followupemail2::campaigns_actions';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var ResponseDataProcessor
     */
    private $responseDataProcessor;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EmailManagementInterface $emailManagement
     * @param ResponseDataProcessor $responseDataProcessor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EmailManagementInterface $emailManagement,
        ResponseDataProcessor $responseDataProcessor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->emailManagement = $emailManagement;
        $this->responseDataProcessor = $responseDataProcessor;
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
            'message'   => __('Unknown error occured!')
        ];

        if ($this->getRequest()->isAjax()) {
            $data = $this->getRequest()->getPostValue();
            if ($data
                && isset($data['event_id'])
                && $data['event_id']
                && isset($data['positions'])
                && \is_array($data['positions'])
            ) {
                try {
                    foreach ($data['positions'] as $emailData) {
                        $this->emailManagement->changePosition($emailData['id'], $emailData['position']);
                    }

                    $result = array_merge(
                        [
                            'error'     => false,
                            'message'   => __('Success.'),
                        ],
                        $this->responseDataProcessor->getPreparedData($data['event_id'])
                    );
                } catch (\Exception $e) {
                    $result = [
                        'error'     => true,
                        'message'   => __($e->getMessage())
                    ];
                }
            } else {
                $result = [
                    'error'     => true,
                    'message'   => __('No data received!')
                ];
            }
        }

        return $resultJson->setData($result);
    }
}
