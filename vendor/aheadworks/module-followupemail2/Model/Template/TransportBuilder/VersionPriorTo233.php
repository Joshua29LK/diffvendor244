<?php
namespace Aheadworks\Followupemail2\Model\Template\TransportBuilder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Aheadworks\Followupemail2\Model\Template\TransportBuilderInterface;
use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Aheadworks\Followupemail2\Model\Statistics\EmailTracker;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class VersionPriorTo233
 *
 * @package Aheadworks\Followupemail2\Model\Template\TransportBuilder
 */
class VersionPriorTo233 extends TransportBuilder implements TransportBuilderInterface
{
    /**
     * Template data
     *
     * @var array
     */
    private $templateData = [];

    /**
     * @var string
     */
    private $messageType = MessageInterface::TYPE_HTML;

    /**
     * @var \Zend_Mime_Part|string
     */
    private $content;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var array
     */
    private $trackingData = [];

    /**
     * @var StatisticsManagementInterface
     */
    private $statisticsManagement;

    /**
     * @var EmailTracker
     */
    private $emailTracker;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param StatisticsManagementInterface $statisticsManagement
     * @param EmailTracker $emailTracker
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        StatisticsManagementInterface $statisticsManagement,
        EmailTracker $emailTracker,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($templateFactory, $message, $senderResolver, $objectManager, $mailTransportFactory);
        $this->statisticsManagement = $statisticsManagement;
        $this->emailTracker = $emailTracker;
        $this->storeManager = $storeManager;
    }

    /**
     * Set template data
     *
     * @param array $data
     * @return $this
     */
    public function setTemplateData($data)
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * Set tracking data
     *
     * @param array $trackingData
     * @return $this
     */
    public function setTrackingData($trackingData)
    {
        $this->trackingData = $trackingData;
        return $this;
    }

    /**
     * Get statistics id
     *
     * @return int|null
     */
    public function getStatisticsId()
    {
        if (isset($this->trackingData['stat_id'])) {
            return $this->trackingData['stat_id'];
        }
        return null;
    }

    /**
     * Get statistics email
     *
     * @return int|null
     */
    public function getStatisticsEmail()
    {
        if (isset($this->trackingData['email'])) {
            return $this->trackingData['email'];
        }
        return null;
    }

    /**
     * Set message type
     *
     * @param string $messageType
     * @return $this
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;
        return $this;
    }

    /**
     * Get message content
     *
     * @return string
     */
    public function getMessageContent()
    {
        return
            $this->content instanceof \Zend_Mime_Part ?
                $this->content->getRawContent() :
                $this->content;
    }

    /**
     * Get message subject
     *
     * @return string
     */
    public function getMessageSubject()
    {
        return $this->subject;
    }

    /**
     * Prepare message
     *
     * @param bool $preview
     * @return $this
     * @throws LocalizedException
     */
    protected function prepareMessage($preview = false)
    {
        $template = $this->getTemplate()->setData($this->templateData);

        if (isset($this->templateOptions['store'])) {
            $storeId = $this->templateOptions['store'];
        } else {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $this->subject = $template->getSubject();
        $this->content = $template->getProcessedTemplate($this->templateVars);

        $content = $this->content;
        if (!$preview && isset($this->trackingData['email']) && isset($this->trackingData['email_content_id'])) {
            /** @var StatisticsHistoryInterface|null $statHistory */
            $statHistory = $this->statisticsManagement->addNew(
                $this->trackingData['email'],
                $this->trackingData['email_content_id']
            );
            if ($statHistory) {
                unset($this->trackingData['email_content_id']);
                $this->trackingData['stat_id'] = $statHistory->getId();
                $content = $this->emailTracker->getPreparedContent($this->content, $this->trackingData, $storeId);
            }
        }

        if ($this->message->getSubject()) {
            $this->message->clearSubject();
        }

        $this->message
            ->setMessageType($this->messageType)
            ->setSubject($this->subject)
            ->setBody($content);

        return $this;
    }

    /**
     * Prepare message for preview
     *
     * @return $this
     * @throws LocalizedException
     */
    public function prepareForPreview()
    {
        $this->prepareMessage(true);
        return $this->reset();
    }
}
