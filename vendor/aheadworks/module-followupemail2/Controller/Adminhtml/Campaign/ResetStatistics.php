<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Campaign;

use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;

/**
 * Class ResetStatistics
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Campaign
 */
class ResetStatistics extends \Magento\Backend\App\Action
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
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StatisticsManagementInterface $statisticsManagement
     * @param FormKey $formKey
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StatisticsManagementInterface $statisticsManagement,
        FormKey $formKey
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->statisticsManagement = $statisticsManagement;
        $this->formKey = $formKey;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
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
            $postData = $this->getRequest()->getPostValue();
            if ($postData
                && isset($postData['id'])
                && $postData['form_key'] == $this->formKey->getFormKey()
            ) {
                try {
                    $this->statisticsManagement->resetByCampaignId($postData['id']);

                    $result = [
                        'error'     => false,
                        'message'   => __('Success.'),
                    ];
                } catch (\Exception $e) {
                    $result = [
                        'error'     => true,
                        'message'   => __($e->getMessage())
                    ];
                }
            }
        }
        return $resultJson->setData($result);
    }
}
