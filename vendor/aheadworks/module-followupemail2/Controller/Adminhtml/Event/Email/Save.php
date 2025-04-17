<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\QueueManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Save
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email
 */
class Save extends \Magento\Backend\App\Action
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
     * EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var EmailInterfaceFactory
     */
    private $emailFactory;

    /**
     * @var QueueManagementInterface
     */
    private $queueManagement;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;
    /**
     * @var ResponseDataProcessor
     */
    private $responseDataProcessor;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EmailRepositoryInterface $emailRepository
     * @param EmailManagementInterface $emailManagement
     * @param EmailInterfaceFactory $emailFactory
     * @param QueueManagementInterface $queueManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param PostDataProcessor $postDataProcessor
     * @param ResponseDataProcessor $responseDataProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EmailRepositoryInterface $emailRepository,
        EmailManagementInterface $emailManagement,
        EmailInterfaceFactory $emailFactory,
        QueueManagementInterface $queueManagement,
        DataObjectHelper $dataObjectHelper,
        PostDataProcessor $postDataProcessor,
        ResponseDataProcessor $responseDataProcessor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->emailRepository = $emailRepository;
        $this->emailManagement = $emailManagement;
        $this->emailFactory = $emailFactory;
        $this->queueManagement = $queueManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->postDataProcessor = $postDataProcessor;
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
            'message'   => __('No data specified!')
        ];
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $id = $data['id'] ?? false;
                $preparedData = $this->postDataProcessor->prepareEntityData($data);
                $emailDataObject = $this->saveEmailData($id, $preparedData);

                if (isset($data['sendtest']) && $data['sendtest']) {
                    $contentId = $data['content_id'] ?? null;
                    $testEmailSent = $this->queueManagement->sendTest($emailDataObject, $contentId);
                } else {
                    $testEmailSent = false;
                }

                $result = array_merge(
                    [
                        'error'         => false,
                        'message'       => __('Success.'),
                        'create'        => $id ? false : true,
                        'continue_edit' => $this->getRequest()->getParam('back') ? $emailDataObject->getId() : false,
                    ],
                    $this->responseDataProcessor->getPreparedData($emailDataObject->getEventId())
                );

                if ($testEmailSent) {
                    $result['message'] = __('Email was successfully sent.');
                    $result['send_test'] = true;
                }
            } catch (\Exception $e) {
                $result = [
                    'error'     => true,
                    'message'   => __($e->getMessage())
                ];
            }
        }
        return $resultJson->setData($result);
    }

    /**
     * Save email data
     *
     * @param int|null $id
     * @param array $data
     * @return EmailInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveEmailData($id, $data)
    {
        /** @var EmailInterface $emailDataObject */
        $emailDataObject = $id
            ? $this->emailRepository->get($id)
            : $this->emailFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $emailDataObject,
            $data,
            EmailInterface::class
        );

        if (!$emailDataObject->getPosition()) {
            $emailDataObject->setPosition(
                $this->emailManagement->getNewEmailPosition($emailDataObject->getEventId())
            );
        }

        /** @var EmailInterface $emailDataObject */
        $emailDataObject = $this->emailRepository->save($emailDataObject);

        return $emailDataObject;
    }
}
