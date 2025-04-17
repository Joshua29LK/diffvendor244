<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\EventManagementInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Move
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event
 */
class Move extends Action
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
     * @var EventManagementInterface
     */
    private $eventManagement;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param EventManagementInterface $eventManagement
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        EventManagementInterface $eventManagement
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eventManagement = $eventManagement;
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
        if ($data && isset($data['event_id'], $data['campaign_id'])) {
            try {
                /** @var EventInterface $event */
                $event = $this->eventManagement->changeCampaign($data['event_id'], $data['campaign_id']);

                $result = [
                    'error'         => false,
                    'message'       => __('Success.'),
                ];

                $this->messageManager->addSuccessMessage(__('Event was moved successfully.'));

                if (isset($data['redirect'])) {
                    $result = array_merge(
                        $result,
                        [
                            'redirect_url'  => $this->getUrl(
                                'aw_followupemail2/event/index/',
                                [
                                    'campaign_id' => $event->getCampaignId(),
                                    '_secure' => $this->getRequest()->isSecure()
                                ]
                            )
                        ]
                    );
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
}
