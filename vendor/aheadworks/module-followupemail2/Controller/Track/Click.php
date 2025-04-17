<?php
namespace Aheadworks\Followupemail2\Controller\Track;

use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Model\Statistics\EmailTracker\Encryptor as EmailTrackerEncryptor;
use Magento\Framework\App\Action\Context;

/**
 * Class Click
 * @package Aheadworks\Followupemail2\Controller\Track
 */
class Click extends \Magento\Framework\App\Action\Action
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
     * @param Context $context
     * @param StatisticsManagementInterface $statisticsManagement
     * @param EmailTrackerEncryptor $emailTrackerEncryptor
     */
    public function __construct(
        Context $context,
        StatisticsManagementInterface $statisticsManagement,
        EmailTrackerEncryptor $emailTrackerEncryptor
    ) {
        parent::__construct($context);
        $this->statisticsManagement = $statisticsManagement;
        $this->emailTrackerEncryptor = $emailTrackerEncryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->getRequest()->getParam('url');
        $params = $this->getRequest()->getParam('params');
        if ($params) {
            $params = $this->emailTrackerEncryptor->decrypt($params);
            if (isset($params['stat_id'], $params['email'])) {
                $this->statisticsManagement->addClicked($params['stat_id'], $params['email']);
            }
            if ($url) {
                $url = $this->decodeUrl($url);
                return $resultRedirect->setUrl($url);
            }
        }
        return $resultRedirect->setRefererOrBaseUrl();
    }

    /**
     * Decode URL to avoid problems with '/' and '%2F'
     *
     * @param string $encodedUrl
     * @return string
     */
    private function decodeUrl($encodedUrl)
    {
        return  urldecode(urldecode($encodedUrl));
    }
}
