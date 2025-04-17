<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue;

use Aheadworks\Followupemail2\Api\EventQueueRepositoryInterface;
use Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue\Preview\ResponseDataProcessor;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Preview
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Queue
 */
class Preview extends \Magento\Backend\App\Action
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
     * EventQueueRepositoryInterface
     */
    private $eventQueueRepository;

    /**
     * @var ResponseDataProcessor
     */
    private $responseDataProcessor;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EventQueueRepositoryInterface $eventQueueRepository
     * @param ResponseDataProcessor $responseDataProcessor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventQueueRepositoryInterface $eventQueueRepository,
        ResponseDataProcessor $responseDataProcessor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventQueueRepository = $eventQueueRepository;
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
                $eventQueueItem = $this->eventQueueRepository->get($id);

                $result = array_merge(
                    [
                        'error'     => false,
                        'message'   => __('Success.'),
                    ],
                    $this->responseDataProcessor->getPreparedData($eventQueueItem)
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
                'message'   => __('Id is not specified!')
            ];
        }
        return $resultJson->setData($result);
    }
}
