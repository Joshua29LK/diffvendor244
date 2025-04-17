<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email
 */
class Delete extends \Magento\Backend\App\Action
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
     * @var ResponseDataProcessor
     */
    private $responseDataProcessor;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EmailRepositoryInterface $emailRepository
     * @param ResponseDataProcessor $responseDataProcessor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EmailRepositoryInterface $emailRepository,
        ResponseDataProcessor $responseDataProcessor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->emailRepository = $emailRepository;
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
                $eventId = $this->getEventId($id);

                $this->emailRepository->deleteById($id);

                $result = array_merge(
                    [
                        'error'     => false,
                        'message'   => __('Success.'),
                    ],
                    $this->responseDataProcessor->getPreparedData($eventId)
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

    /**
     * Get event id
     *
     * @param int $emailId
     * @return int|null
     * @throws NoSuchEntityException
     */
    private function getEventId($emailId)
    {
        return $this->emailRepository->get($emailId)->getEventId();
    }
}
