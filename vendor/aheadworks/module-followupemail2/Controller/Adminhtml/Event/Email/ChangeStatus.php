<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class ChangeStatus
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email
 */
class ChangeStatus extends \Magento\Backend\App\Action
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
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var EmailInterface $email */
                $email = $this->emailManagement->changeStatus($id);

                $result = array_merge(
                    [
                        'error'     => false,
                        'message'   => __('Success.'),
                    ],
                    $this->responseDataProcessor->getPreparedData($email->getEventId())
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
                'message'   => __('Email Id is not specified!')
            ];
        }
        return $resultJson->setData($result);
    }
}
