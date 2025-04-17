<?php
namespace Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email;

use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Api\Data\PreviewInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\EmailManagementInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Store\Model\Store;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Preview
 * @package Aheadworks\Followupemail2\Controller\Adminhtml\Event\Email
 */
class Preview extends \Magento\Backend\App\Action
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
     * @var Config
     */
    private $config;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var EmailManagementInterface
     */
    private $emailManagement;

    /**
     * @var EmailContentInterfaceFactory
     */
    private $emailContentFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Config $config
     * @param Registry $coreRegistry
     * @param EventRepositoryInterface $eventRepository
     * @param EmailManagementInterface $emailManagement
     * @param EmailContentInterfaceFactory $emailContentFactory
     * @param FormKey $formKey
     * @param DataObjectHelper $dataObjectHelper
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Config $config,
        Registry $coreRegistry,
        EventRepositoryInterface $eventRepository,
        EmailManagementInterface $emailManagement,
        EmailContentInterfaceFactory $emailContentFactory,
        FormKey $formKey,
        DataObjectHelper $dataObjectHelper,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
        $this->coreRegistry = $coreRegistry;
        $this->eventRepository = $eventRepository;
        $this->emailManagement = $emailManagement;
        $this->emailContentFactory = $emailContentFactory;
        $this->formKey = $formKey;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->layoutFactory = $layoutFactory;
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
            $previewData = $this->getRequest()->getPostValue();
            if ($previewData
                && isset($previewData['email_data'], $previewData['form_key']) &&
                $previewData['form_key'] == $this->formKey->getFormKey()
            ) {
                try {
                    $preview = $this->getPreview($previewData['email_data']);
                    $this->coreRegistry->register('aw_followupemail2_preview', $preview);

                    $renderedPreview = $this->layoutFactory->create()
                        ->createBlock(\Aheadworks\Followupemail2\Block\Adminhtml\Preview::class)
                        ->setTemplate('Aheadworks_Followupemail2::preview.phtml')
                        ->toHtml();

                    $result = [
                        'error'     => false,
                        'message'   => __('Success.'),
                        'preview'     => $renderedPreview
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

    /**
     * Get preview data
     *
     * @param array $emailData
     * @return PreviewInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getPreview($emailData)
    {
        /** @var EventInterface $event */
        $event = $this->eventRepository->get($emailData['event_id']);
        $storeIds = $event->getStoreIds();
        if (count($storeIds) > 0) {
            $storeId = array_shift($storeIds);
        } else {
            $storeId = Store::DEFAULT_STORE_ID;
        }

        if (isset($emailData['content']['use_config'])) {
            $emailData['content'] = $this->prepareContent($emailData['content'], $storeId);
        }

        /** @var EmailContentInterface $contentDataObject */
        $contentDataObject = $this->emailContentFactory->create();
        if (isset($emailData['content'])) {
            $this->dataObjectHelper->populateWithArray(
                $contentDataObject,
                $emailData['content'],
                EmailContentInterface::class
            );
        }

        return $this->emailManagement->getPreview($storeId, $contentDataObject);
    }

    /**
     * Prepare email content data
     *
     * @param array $content
     * @param int $storeId
     * @return array
     */
    private function prepareContent($content, $storeId)
    {
        $useConfig = $content['use_config'];
        if ($useConfig[EmailContentInterface::SENDER_NAME]) {
            $content[EmailContentInterface::SENDER_NAME] = $this->config->getSenderName($storeId);
        }
        if ($useConfig[EmailContentInterface::SENDER_EMAIL]) {
            $content[EmailContentInterface::SENDER_EMAIL] = $this->config->getSenderEmail($storeId);
        }
        if ($useConfig[EmailContentInterface::HEADER_TEMPLATE]) {
            $content[EmailContentInterface::HEADER_TEMPLATE] =
                $this->config->getEmailHeaderTemplate($storeId);
        }
        if ($useConfig[EmailContentInterface::FOOTER_TEMPLATE]) {
            $content[EmailContentInterface::FOOTER_TEMPLATE] =
                $this->config->getEmailFooterTemplate($storeId);
        }
        unset($content['use_config']);

        return $content;
    }
}
