<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Campaign;

use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Delete
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Campaign
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
     * CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->campaignRepository = $campaignRepository;
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
                $this->campaignRepository->deleteById($id);
                $result = [
                    'error'     => false,
                    'message'   => __('Success.')
                ];
            } catch (\Exception $e) {
                $result = [
                    'error'     => true,
                    'message'   => __($e->getMessage())
                ];
            }
        } else {
            $result = [
                'error'     => true,
                'message'   => __('Campaign Id is not specified!')
            ];
        }
        return $resultJson->setData($result);
    }
}
