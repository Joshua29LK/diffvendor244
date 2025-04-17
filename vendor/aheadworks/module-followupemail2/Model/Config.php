<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Model\ResourceModel\Config as ConfigResource;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class Config
 * @package Aheadworks\Followupemail2\Model
 */
class Config extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Configuration path to enable module parameter
     */
    const XML_PATH_MODULE_OUTPUT_DISABLED = 'advanced/modules_disable_output/Aheadworks_Followupemail2';

    /**
     * Configuration path to sender
     */
    const XML_PATH_SENDER = 'followupemailtwo/general/sender';

    /**
     * Configuration path to test email recipient
     */
    const XML_PATH_TEST_EMAIL_RECIPIENT = 'followupemailtwo/general/testemail';

    /**
     * Configuration path to enable test mode
     */
    const XML_PATH_ENABLE_TEST_MODE = 'followupemailtwo/general/enabletestmode';

    /**
     * Configuration path to BCC email address(es)
     */
    const XML_PATH_BCC_EMAIL_ADDRESSES = 'followupemailtwo/general/bcc_email_addresses';

    /**
     * Configuration path to mail log keep emails for
     */
    const XML_PATH_MAIL_LOG_KEEP_FOR = 'followupemailtwo/maillog/keepfor';

    /**
     * Configuration path to email header template
     */
    const XML_PATH_EMAIL_HEADER_TEMPLATE = 'followupemailtwo/header_and_footer/email_header_template';

    /**
     * Configuration path to email footer template
     */
    const XML_PATH_EMAIL_FOOTER_TEMPLATE = 'followupemailtwo/header_and_footer/email_footer_template';

    /**
     * Process event history last exec time
     */
    const PROCESS_EVENT_HISTORY_LAST_EXEC_TIME = 'process_event_history_last_exec_time';

    /**
     * Process event queue last exec time
     */
    const PROCESS_EVENT_QUEUE_LAST_EXEC_TIME = 'process_event_queue_last_exec_time';

    /**
     * Send emails last exec time
     */
    const SEND_EMAILS_LAST_EXEC_TIME = 'send_emails_last_exec_time';

    /**
     * Clear log last exec time
     */
    const CLEAR_LOG_LAST_EXEC_TIME = 'clear_log_last_exec_time';

    /**
     * Process birthdays last exec time
     */
    const PROCESS_BIRTHDAYS_LAST_EXEC_TIME = 'process_birthdays_last_exec_time';

    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ConfigResource $configResource
     * @param ScopeConfigInterface $scopeConfig
     * @param SenderResolverInterface $senderResolver
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigResource $configResource,
        ScopeConfigInterface $scopeConfig,
        SenderResolverInterface $senderResolver,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->configResource = $configResource;
        $this->scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ConfigResource::class);
    }

    /**
     * Is module output enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return !$this->scopeConfig->isSetFlag(
            self::XML_PATH_MODULE_OUTPUT_DISABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get sender
     * @param int|null $storeId
     * @return string
     */
    public function getSender($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get sender email
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSenderEmail($storeId = null)
    {
        $sender = $this->getSender($storeId);
        $data = $this->senderResolver->resolve($sender, $storeId);

        return $data['email'];
    }

    /**
     * Get sender name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSenderName($storeId = null)
    {
        $sender = $this->getSender($storeId);
        $data = $this->senderResolver->resolve($sender, $storeId);

        return $data['name'];
    }

    /**
     * Get test recipient email
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTestEmailRecipient($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TEST_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if test mode is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isTestModeEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_TEST_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get BCC email addresses
     *
     * @param int|null $storeId
     * @return array
     */
    public function getBCCEmailAddresses($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BCC_EMAIL_ADDRESSES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get keep emails for
     *
     * @return string
     */
    public function getKeepEmailsFor()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAIL_LOG_KEEP_FOR
        );
    }

    /**
     * Get email header template
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEmailHeaderTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_HEADER_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email footer template
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEmailFooterTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_FOOTER_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get process event history last exec time
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getProcessEventHistoryLastExecTime()
    {
        return (int)$this->getParam(self::PROCESS_EVENT_HISTORY_LAST_EXEC_TIME);
    }

    /**
     * Set process event history last exec time
     *
     * @param int $timestamp
     * @return $this
     * @codeCoverageIgnore
     */
    public function setProcessEventHistoryLastExecTime($timestamp)
    {
        $this->setParam(self::PROCESS_EVENT_HISTORY_LAST_EXEC_TIME, $timestamp);
        return $this;
    }

    /**
     * Get process event queue last exec time
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getProcessEventQueueLastExecTime()
    {
        return (int)$this->getParam(self::PROCESS_EVENT_QUEUE_LAST_EXEC_TIME);
    }

    /**
     * Set process event queue last exec time
     *
     * @param int $timestamp
     * @return $this
     * @codeCoverageIgnore
     */
    public function setProcessEventQueueLastExecTime($timestamp)
    {
        $this->setParam(self::PROCESS_EVENT_QUEUE_LAST_EXEC_TIME, $timestamp);
        return $this;
    }

    /**
     * Get send emails last exec time
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getSendEmailsLastExecTime()
    {
        return (int)$this->getParam(self::SEND_EMAILS_LAST_EXEC_TIME);
    }

    /**
     * Set send emails last exec time
     *
     * @param int $timestamp
     * @return $this
     * @codeCoverageIgnore
     */
    public function setSendEmailsLastExecTime($timestamp)
    {
        $this->setParam(self::SEND_EMAILS_LAST_EXEC_TIME, $timestamp);
        return $this;
    }

    /**
     * Get clear log last exec time
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getClearLogLastExecTime()
    {
        return (int)$this->getParam(self::CLEAR_LOG_LAST_EXEC_TIME);
    }

    /**
     * Set clear log last exec time
     *
     * @param int $timestamp
     * @return $this
     * @codeCoverageIgnore
     */
    public function setClearLogLastExecTime($timestamp)
    {
        $this->setParam(self::CLEAR_LOG_LAST_EXEC_TIME, $timestamp);
        return $this;
    }

    /**
     * Get process birthdays last exec time
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getProcessBirthdaysLastExecTime()
    {
        return (int)$this->getParam(self::PROCESS_BIRTHDAYS_LAST_EXEC_TIME);
    }

    /**
     * Set process birthdays last exec time
     *
     * @param int $timestamp
     * @return $this
     * @codeCoverageIgnore
     */
    public function setProcessBirthdaysLastExecTime($timestamp)
    {
        $this->setParam(self::PROCESS_BIRTHDAYS_LAST_EXEC_TIME, $timestamp);
        return $this;
    }

    /**
     * Set param
     *
     * @param string $name
     * @param string $value
     * @return $this
     * @codeCoverageIgnore
     */
    private function setParam($name, $value)
    {
        $this->unsetData();
        $this->configResource->load($this, $name, 'name');
        $this->addData([
                'name' => $name,
                'value' => $value
            ]);
        $this->configResource->save($this);

        return $this;
    }

    /**
     * Get param
     *
     * @param string $name
     * @return string $value
     * @codeCoverageIgnore
     */
    private function getParam($name)
    {
        $this->unsetData();
        $this->configResource->load($this, $name, 'name');

        return $this->getData('value');
    }
}
