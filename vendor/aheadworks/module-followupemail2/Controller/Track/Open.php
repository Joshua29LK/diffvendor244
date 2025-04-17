<?php
namespace Aheadworks\Followupemail2\Controller\Track;

use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Model\Statistics\EmailTracker\Encryptor as EmailTrackerEncryptor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Class Open
 * @package Aheadworks\Followupemail2\Controller\Track
 */
class Open extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var EmailTrackerEncryptor
     */
    private $emailTrackerEncryptor;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @param Context $context
     * @param StatisticsManagementInterface $statisticsManagement
     * @param EmailTrackerEncryptor $emailTrackerEncryptor
     * @param AssetRepository $assetRepository
     */
    public function __construct(
        Context $context,
        StatisticsManagementInterface $statisticsManagement,
        EmailTrackerEncryptor $emailTrackerEncryptor,
        AssetRepository $assetRepository
    ) {
        parent::__construct($context);
        $this->statisticsManagement = $statisticsManagement;
        $this->emailTrackerEncryptor = $emailTrackerEncryptor;
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $params = $this->getRequest()->getParam('params');
        if ($params) {
            $params = $this->emailTrackerEncryptor->decrypt($params);
            if (isset($params['stat_id'], $params['email'])) {
                $this->statisticsManagement->addOpened($params['stat_id'], $params['email']);
            }
            $imageUrl = $this->assetRepository->getUrl('spacer.gif');
            return $resultRedirect->setUrl($imageUrl);
        }
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
