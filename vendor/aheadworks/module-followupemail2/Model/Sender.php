<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventQueueInterface;
use Aheadworks\Followupemail2\Api\Data\QueueInterface;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Aheadworks\Followupemail2\Model\Template\TransportBuilder\Version233;
use Aheadworks\Followupemail2\Model\Template\TransportBuilder\VersionPriorTo233;
use Aheadworks\Followupemail2\Model\Template\TransportBuilderInterface;
use Aheadworks\Followupemail2\Model\Template\TransportBuilder\Factory as TransportBuilderFactory;
use Aheadworks\Followupemail2\Model\Template\Variable as TemplateVariable;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Email\Model\TemplateFactory;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Aheadworks\Followupemail2\Model\Email\Address\Validator as EmailValidator;
use Aheadworks\Followupemail2\Model\Email\Address\EmailAddressProcessor;

/**
 * Class Sender
 * @package Aheadworks\Followupemail2\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sender
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransportBuilderInterface|VersionPriorTo233|Version233
     */
    private $transportBuilder;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var TemplateVariable
     */
    private $templateVariable;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var EmailAddressProcessor
     */
    private $emailAddressProcessor;

    /**
     * @param Config $config
     * @param TransportBuilderFactory $transportBuilderFactory
     * @param EventRepositoryInterface $eventRepository
     * @param StatisticsManagementInterface $statisticsManagement
     * @param TemplateFactory $templateFactory
     * @param TemplateVariable $templateVariable
     * @param AppEmulation $appEmulation
     * @param Serializer $serializer
     * @param EmailValidator $emailValidator
     * @param EmailAddressProcessor $emailAddressProcessor
     */
    public function __construct(
        Config $config,
        TransportBuilderFactory $transportBuilderFactory,
        EventRepositoryInterface $eventRepository,
        StatisticsManagementInterface $statisticsManagement,
        TemplateFactory $templateFactory,
        TemplateVariable $templateVariable,
        AppEmulation $appEmulation,
        Serializer $serializer,
        EmailValidator $emailValidator,
        EmailAddressProcessor $emailAddressProcessor
    ) {
        $this->config = $config;
        $this->transportBuilder = $transportBuilderFactory->create();
        $this->eventRepository = $eventRepository;
        $this->statisticsManagement = $statisticsManagement;
        $this->templateFactory = $templateFactory;
        $this->templateVariable = $templateVariable;
        $this->appEmulation = $appEmulation;
        $this->serializer = $serializer;
        $this->emailValidator = $emailValidator;
        $this->emailAddressProcessor = $emailAddressProcessor;
    }

    /**
     * Send queue item
     *
     * @param QueueInterface $queueItem
     * @return QueueInterface
     * @throws MailException
     */
    public function sendQueueItem(QueueInterface $queueItem)
    {
        $storeId = $queueItem->getStoreId();
        if ($this->config->isTestModeEnabled($storeId)) {
            $recipientEmail = $this->config->getTestEmailRecipient($storeId);
        } else {
            $recipientEmail = $queueItem->getRecipientEmail();
        }

        $bcc = '';
        try {
            /** @var EventInterface $event */
            $event = $this->eventRepository->get($queueItem->getEventId());
            if ($event->getBccEmails()) {
                $bcc = $event->getBccEmails();
            } else if ($this->config->getBCCEmailAddresses($storeId)) {
                $bcc = $this->config->getBCCEmailAddresses($storeId);
            }
        } catch (NoSuchEntityException $e) {
        }

        $trackingData = [
            'email' => $recipientEmail,
            'email_content_id' => $queueItem->getEmailContentId()
        ];

        $this->sendEmail(
            $queueItem->getSenderEmail(),
            $queueItem->getSenderName(),
            $recipientEmail,
            $queueItem->getRecipientName(),
            $queueItem->getSubject(),
            $queueItem->getContent(),
            $queueItem->getStoreId(),
            $bcc,
            [],
            $trackingData
        );

        return $queueItem;
    }

    /**
     * Render event queue item
     *
     * @param EventQueueInterface $eventQueueItem
     * @param EmailContentInterface $emailContent
     * @return QueueInterface
     * @throws MailException
     */
    public function renderEventQueueItem(EventQueueInterface $eventQueueItem, EmailContentInterface $emailContent)
    {
        $eventData = $this->serializer->unserialize($eventQueueItem->getEventData());
        $storeId = $eventData['store_id'];

        $emailData = $this->templateVariable->getVariableData($eventQueueItem);

        if ($this->config->isTestModeEnabled($storeId)) {
            $recipientEmail = $this->config->getTestEmailRecipient($storeId);
        } else {
            $recipientEmail = $eventData['email'];
        }

        $header = '';
        $headerTemplate = $emailContent->getHeaderTemplate() ?
            $emailContent->getHeaderTemplate() :
            $this->config->getEmailHeaderTemplate($storeId);

        if ($headerTemplate && $headerTemplate != EmailInterface::NO_TEMPLATE) {
            $header = $this->getHeaderTemplate($headerTemplate, $storeId);
        }

        $footer = '';
        $footerTemplate = $emailContent->getFooterTemplate() ?
            $emailContent->getFooterTemplate() :
            $this->config->getEmailFooterTemplate($storeId);
        if ($footerTemplate && $footerTemplate != EmailInterface::NO_TEMPLATE) {
            $footer = $this->getFooterTemplate($footerTemplate, $storeId);
        }

        $recipientName = isset($emailData['customer_name']) ? $emailData['customer_name'] : '';
        $this->transportBuilder
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ])
            ->setTemplateVars($emailData)
            ->setTemplateData([
                'template_subject' => $emailContent->getSubject(),
                'template_text' => $header . $emailContent->getContent() . $footer
            ])
            ->addTo($recipientEmail, $recipientName)
        ;

        $this->transportBuilder->prepareForPreview();

        $result = [];
        $result['recipient_name'] = $recipientName;
        $result['recipient_email'] = $recipientEmail;
        $result['subject'] = $this->transportBuilder->getMessageSubject();
        $result['content'] = $this->transportBuilder->getMessageContent();

        return $result;
    }

    /**
     * Send email
     *
     * @param string $senderEmail
     * @param string $senderName
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $subject
     * @param string $content
     * @param int $storeId
     * @param string[]|string $bcc
     * @param array $emailData
     * @param array $trackingData
     * @return array (['subject' => ..., 'content' => ...])
     */
    public function sendEmail(
        $senderEmail,
        $senderName,
        $recipientEmail,
        $recipientName,
        $subject,
        $content,
        $storeId,
        $bcc,
        $emailData = [],
        $trackingData = []
    ) {
        $this->transportBuilder
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ])
            ->setTemplateVars($emailData)
            ->setTemplateData([
                'template_subject' => $subject,
                'template_text' => $content
            ])
            ->setTrackingData($trackingData)
            ->setFrom(
                [
                    'name' => $senderName,
                    'email' => $senderEmail
                ]
            )->addTo($recipientEmail, $recipientName)
        ;

        $bcc = $this->emailAddressProcessor->convertToArray($bcc);
        $bcc = $this->emailValidator->getValidEmails($bcc);
        if (count($bcc) > 0) {
            $this->transportBuilder->addBcc($bcc);
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $result = [];
        $result['subject'] = $this->transportBuilder->getMessageSubject();
        $result['content'] = $this->transportBuilder->getMessageContent();

        if ($this->transportBuilder->getStatisticsId() && $this->transportBuilder->getStatisticsEmail()) {
            $this->statisticsManagement->addSent(
                $this->transportBuilder->getStatisticsId(),
                $this->transportBuilder->getStatisticsEmail()
            );
        }

        return $result;
    }

    /**
     * Get prepared content for preview (test data)
     *
     * @param int $storeId
     * @param EmailContentInterface $emailContent
     * @return array ('subject' => ..., 'content' => ...)
     */
    public function getTestPreview($storeId, $emailContent)
    {
        $emailData = $this->templateVariable->getTestVariableData($storeId);

        if ($this->config->getTestEmailRecipient($storeId)) {
            $recipientEmail = $this->config->getTestEmailRecipient($storeId);
        } elseif (isset($emailData['customer_email'])) {
            $recipientEmail = $emailData['customer_email'];
        } else {
            $recipientEmail = '';
        }

        $header = '';
        if ($emailContent->getHeaderTemplate() && $emailContent->getHeaderTemplate() != EmailInterface::NO_TEMPLATE) {
            $header = $this->getHeaderTemplate($emailContent->getHeaderTemplate(), $storeId);
        }

        $footer = '';
        if ($emailContent->getFooterTemplate() && $emailContent->getFooterTemplate() != EmailInterface::NO_TEMPLATE) {
            $footer = $this->getFooterTemplate($emailContent->getFooterTemplate(), $storeId);
        }

        $recipientName = isset($emailData['customer_name']) ? $emailData['customer_name'] : '';
        $this->transportBuilder
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ])
            ->setTemplateVars($emailData)
            ->setTemplateData([
                'template_subject' => $emailContent->getSubject(),
                'template_text' => $header . $emailContent->getContent() . $footer
            ])
            ->addTo($recipientEmail, $recipientName)
        ;

        $this->transportBuilder->prepareForPreview();

        $result = [];
        $result['recipient_name'] = $recipientName;
        $result['recipient_email'] = $recipientEmail;
        $result['subject'] = $this->transportBuilder->getMessageSubject();
        $result['content'] = $this->transportBuilder->getMessageContent();

        return $result;
    }

    /**
     * Get header template
     *
     * @param int|string $templateId
     * @param int $storeId
     * @return string
     */
    private function getHeaderTemplate($templateId, $storeId)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        /** @var Template $template */
        $headerTemplate = $this->templateFactory->create();
        if (is_numeric($templateId)) {
            $headerTemplate->load($templateId);
        } else {
            $headerTemplate->loadDefault($templateId);
        }
        $templateText = $headerTemplate->getTemplateText();
        $this->appEmulation->stopEnvironmentEmulation();

        return $templateText;
    }

    /**
     * Get footer template
     *
     * @param int $templateId
     * @param int $storeId
     * @return string
     */
    private function getFooterTemplate($templateId, $storeId)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $footerTemplate = $this->templateFactory->create();
        if (is_numeric($templateId)) {
            $footerTemplate->load($templateId);
        } else {
            $footerTemplate->loadDefault($templateId);
        }
        $templateText = $footerTemplate->getTemplateText();
        $this->appEmulation->stopEnvironmentEmulation();

        return $templateText;
    }

    /**
     * Send test email
     *
     * @param QueueInterface $queueItem
     * @param EmailContentInterface $emailContent
     * @throws \Exception
     * @return QueueInterface
     */
    public function sendTestEmail(QueueInterface $queueItem, EmailContentInterface $emailContent)
    {
        $storeId = $queueItem->getStoreId();

        $recipientEmail = $this->config->getTestEmailRecipient($storeId);
        if (!$recipientEmail) {
            throw new \Exception(
                __('Unable to send test email. Test Email Recipient is not specified.')
            );
        }
        $bcc = $this->config->getBCCEmailAddresses($storeId);
        $emailData = $this->templateVariable->getTestVariableData($storeId);
        $recipientName = isset($emailData['customer_name']) ? $emailData['customer_name'] : '';

        $senderEmail = $emailContent->getSenderEmail() ?
            $emailContent->getSenderEmail() :
            $this->config->getSenderEmail($storeId);
        $senderName = $emailContent->getSenderName() ?
            $emailContent->getSenderName() :
            $this->config->getSenderName($storeId);

        $header = '';
        $headerTemplate = $emailContent->getHeaderTemplate() ?
            $emailContent->getHeaderTemplate() :
            $this->config->getEmailHeaderTemplate($storeId);

        if ($headerTemplate && $headerTemplate != EmailInterface::NO_TEMPLATE) {
            $header = $this->getHeaderTemplate($headerTemplate, $storeId);
        }

        $footer = '';
        $footerTemplate = $emailContent->getFooterTemplate() ?
            $emailContent->getFooterTemplate() :
            $this->config->getEmailFooterTemplate($storeId);

        if ($footerTemplate && $footerTemplate!= EmailInterface::NO_TEMPLATE) {
            $footer = $this->getFooterTemplate($footerTemplate, $storeId);
        }

        $trackingData = [
            'email' => $recipientEmail,
            'email_content_id' => $queueItem->getEmailContentId()
        ];

        $result = $this->sendEmail(
            $senderEmail,
            $senderName,
            $recipientEmail,
            $recipientName,
            '[TEST EMAIL] '. $emailContent->getSubject(),
            $header . $emailContent->getContent() . $footer,
            $storeId,
            $bcc,
            $emailData,
            $trackingData
        );

        $queueItem
            ->setSenderEmail($senderEmail)
            ->setSenderName($senderName)
            ->setRecipientEmail($recipientEmail)
            ->setRecipientName($recipientName)
            ->setSubject($result['subject'])
            ->setContent($result['content']);

        return $queueItem;
    }
}
