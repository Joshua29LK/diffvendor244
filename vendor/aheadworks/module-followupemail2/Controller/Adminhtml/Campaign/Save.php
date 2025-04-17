<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Campaign;

use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterfaceFactory;
use Aheadworks\Followupemail2\Api\CampaignManagementInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;

/**
 * Class Save
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Campaign
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
     * CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var CampaignInterfaceFactory
     */
    private $campaignFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CampaignManagementInterface
     */
    private $campaignManagement;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CampaignRepositoryInterface $campaignRepository
     * @param CampaignInterfaceFactory $campaignFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param CampaignManagementInterface $campaignManagement
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CampaignRepositoryInterface $campaignRepository,
        CampaignInterfaceFactory $campaignFactory,
        DataObjectHelper $dataObjectHelper,
        CampaignManagementInterface $campaignManagement
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->campaignRepository = $campaignRepository;
        $this->campaignFactory = $campaignFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->campaignManagement = $campaignManagement;
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

                /** @var CampaignInterface $campaignDataObject */
                $campaignDataObject = $id
                    ? $this->campaignRepository->get($id)
                    : $this->campaignFactory->create();

                $this->dataObjectHelper->populateWithArray(
                    $campaignDataObject,
                    $data,
                    CampaignInterface::class
                );

                $campaignDataObject = $this->prepareDataObject($campaignDataObject);

                $this->campaignRepository->save($campaignDataObject);

                if (isset($data['duplicate_id']) && $data['duplicate_id']) {
                    $this->campaignManagement->duplicateCampaignEvents(
                        $data['duplicate_id'],
                        $campaignDataObject->getId()
                    );
                }

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
        }
        return $resultJson->setData($result);
    }

    /**
     * Prepare data object before save
     *
     * @param CampaignInterface $dataObject
     * @return CampaignInterface
     * @throws \Exception
     */
    private function prepareDataObject($dataObject)
    {
        $startDate = $dataObject->getStartDate();
        $dataObject->setStartDate($this->getPreparedDate($startDate));
        $endDate = $dataObject->getEndDate();
        $dataObject->setEndDate($this->getPreparedDate($endDate));
        return $dataObject;
    }

    /**
     * Retrieve date, prepared for saving into database
     *
     * @param string $date
     * @return string|null
     * @throws \Exception
     */
    private function getPreparedDate($date)
    {
        $preparedDate = null;
        if ($date != '') {
            $date = new \DateTime($date ?? 'now');
            $preparedDate = $date->format(StdlibDateTime::DATETIME_PHP_FORMAT);
        }
        return $preparedDate;
    }
}
