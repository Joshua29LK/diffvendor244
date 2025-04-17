<?php
namespace Aheadworks\Followupemail2\Model\Template\TransportBuilder;

use Aheadworks\Followupemail2\Api\Data\StatisticsHistoryInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;
use Magento\Framework\Mail\MimeMessage;
use Magento\Framework\Mail\Template\TransportBuilder;
use Aheadworks\Followupemail2\Model\Template\TransportBuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Followupemail2\Model\Statistics\EmailTracker;
use Aheadworks\Followupemail2\Api\StatisticsManagementInterface;

/**
 * Class Version233
 *
 * @package Aheadworks\Followupemail2\Model\Template\TransportBuilder
 */
class Version233 extends TransportBuilder implements TransportBuilderInterface
{
    /**
     * Template data
     *
     * @var array
     */
    private $templateData = [];

    /**
     * @var array
     */
    private $trackingData = [];

    /**
     * @var string
     */
    private $messageType = MessageInterface::TYPE_HTML;

    /**
     * @var MimeMessage|string
     */
    private $content;

    /**
     * @var string
     */
    private $subject;

    /**
     * Param that used for storing all message data until it will be used
     *
     * @var array
     */
    protected $messageData = [];

    /**
     * @var EmailMessageInterfaceFactory
     */
    protected $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    protected $mimeMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    protected $mimePartInterfaceFactory;

    /**
     * @var AddressConverter
     */
    protected $addressConverter;

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
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory
        );
        $this->emailMessageInterfaceFactory = $this->objectManager->get(EmailMessageInterfaceFactory::class);
        $this->mimeMessageInterfaceFactory = $this->objectManager->get(MimeMessageInterfaceFactory::class);
        $this->mimePartInterfaceFactory = $this->objectManager->get(MimePartInterfaceFactory::class);
        $this->addressConverter = $this->objectManager->get(AddressConverter::class);
        $this->statisticsManagement = $statisticsManagement;
        $this->emailTracker = $emailTracker;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function addCc($address, $name = '')
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTo($address, $name = '')
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addBcc($address)
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($email, $name = null)
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws MailException
     */
    public function setFrom($from)
    {
        return $this->setFromByScope($from, null);
    }

    /**
     * @inheritdoc
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);

        return $this;
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
        $result = $this->content;
        if ($this->content instanceof \Zend_Mime_Part) {
            $result = $this->content->getRawContent();
        }

        if ($this->content instanceof MimeMessage) {
            $result = $this->content->getMessage();
        }

        return $result;
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
     * @inheritdoc
     */
    protected function reset()
    {
        $this->messageData = [];
        return parent::reset();
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
     * Prepare message
     *
     * @param bool $preview
     * @return $this
     * @throws LocalizedException
     */
    protected function prepareMessage($preview = false)
    {
        $template = $this->getTemplate()->setData($this->templateData);
        if ($template->getType() != TemplateTypesInterface::TYPE_TEXT
            && $template->getType() != TemplateTypesInterface::TYPE_HTML
        ) {
            throw new LocalizedException(
                new Phrase('Unknown template type')
            );
        }

        if (isset($this->templateOptions['store'])) {
            $storeId = $this->templateOptions['store'];
        } else {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $this->messageData['subject'] = html_entity_decode(
            (string)$template->getSubject(),
            ENT_QUOTES
        );

        $content = $template->getProcessedTemplate($this->templateVars);
        if (!$preview && isset($this->trackingData['email']) && isset($this->trackingData['email_content_id'])) {
            /** @var StatisticsHistoryInterface|null $statHistory */
            $statHistory = $this->statisticsManagement->addNew(
                $this->trackingData['email'],
                $this->trackingData['email_content_id']
            );
            if ($statHistory) {
                unset($this->trackingData['email_content_id']);
                $this->trackingData['stat_id'] = $statHistory->getId();
                $content = $this->emailTracker->getPreparedContent($content, $this->trackingData, $storeId);
            }
        }
        $mimePart = $this->mimePartInterfaceFactory->create(
            ['content' => $content]
        );
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => [$mimePart]]
        );

        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);
        $this->content = $content;
        $this->subject = $template->getSubject();

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

    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param string|array $email
     * @param string|null $name
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function addAddressByType(string $addressType, $email, ?string $name = null)
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        } else {
            $this->messageData[$addressType] = $convertedAddressArray;
        }
    }
}
