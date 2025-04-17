<?php
namespace Aheadworks\Followupemail2\Test\Unit\Model;

use Aheadworks\Followupemail2\Model\Config;
use Aheadworks\Followupemail2\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;

/**
 * Test for \Aheadworks\Followupemail2\Model\Config
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $configModel;

    /**
     * @var ConfigResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configResourceMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var SenderResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $senderResolverMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp() : void
    {
        $objectManager = new ObjectManager($this);
        $this->configResourceMock = $this->getMockBuilder(ConfigResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->senderResolverMock = $this->getMockForAbstractClass(SenderResolverInterface::class);

        $this->configModel = $objectManager->getObject(
            Config::class,
            [
                'configResource' => $this->configResourceMock,
                'scopeConfig' => $this->scopeConfigMock,
                'senderResolver' => $this->senderResolverMock
            ]
        );
    }

    /**
     * Test isEnabled method
     */
    public function testIsEnabled()
    {
        $storeId = 1;
        $moduleOutputDisabled = false;
        $moduleEnabled = true;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_MODULE_OUTPUT_DISABLED, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($moduleOutputDisabled);
        $this->assertEquals($moduleEnabled, $this->configModel->isEnabled($storeId));
    }

    /**
     * Test getSender method
     */
    public function testGetSender()
    {
        $storeId = 1;
        $emailSender = 'general';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SENDER, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($emailSender);
        $this->assertEquals($emailSender, $this->configModel->getSender($storeId));
    }

    /**
     * Test getSenderEmail method
     */
    public function testGetSenderEmail()
    {
        $storeId = 1;
        $emailSender = 'general';
        $sender = [
            'name' => 'Store Email',
            'email' => 'store_email@example.com'
        ];
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SENDER, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($emailSender);

        $this->senderResolverMock->expects($this->once())
            ->method('resolve')
            ->with($emailSender, $storeId)
            ->willReturn($sender);

        $this->assertEquals($sender['email'], $this->configModel->getSenderEmail($storeId));
    }

    /**
     * Test getSenderName method
     */
    public function testGetSenderName()
    {
        $storeId = 1;
        $emailSender = 'general';
        $sender = [
            'name' => 'Store Email',
            'email' => 'store_email@example.com'
        ];
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_SENDER, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($emailSender);

        $this->senderResolverMock->expects($this->once())
            ->method('resolve')
            ->with($emailSender, $storeId)
            ->willReturn($sender);

        $this->assertEquals($sender['name'], $this->configModel->getSenderName($storeId));
    }

    /**
     * Test getTestEmailRecipient method
     */
    public function testGetTestEmailRecipient()
    {
        $storeId = 1;
        $email = 'test_email@example.com';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_TEST_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($email);
        $this->assertEquals($email, $this->configModel->getTestEmailRecipient($storeId));
    }

    /**
     * Test isTestModeEnabled method
     */
    public function testIsTestModeEnabled()
    {
        $storeId = 1;
        $testModeEnabled = true;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ENABLE_TEST_MODE, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($testModeEnabled);
        $this->assertEquals($testModeEnabled, $this->configModel->isTestModeEnabled($storeId));
    }

    /**
     * Test getBCCEmailAddresses method
     */
    public function testGetBCCEmailAddresses()
    {
        $storeId = 1;
        $bccParam = 'bcc_email1@example.com, bcc_email2@example.com,bcc_email3@example.com';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_BCC_EMAIL_ADDRESSES, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($bccParam);

        $this->assertEquals($bccParam, $this->configModel->getBCCEmailAddresses($storeId));
    }

    /**
     * Test getKeepEmailsFor method
     */
    public function testGetKeepEmailsFor()
    {
        $keepEmailsFor = 60;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_MAIL_LOG_KEEP_FOR)
            ->willReturn($keepEmailsFor);
        $this->assertEquals($keepEmailsFor, $this->configModel->getKeepEmailsFor());
    }

    /**
     * Test getEmailHeaderTemplate method
     */
    public function testGetEmailHeaderTemplate()
    {
        $storeId = 1;
        $value = 'design_email_header_template';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_EMAIL_HEADER_TEMPLATE, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->getEmailHeaderTemplate($storeId));
    }

    /**
     * Test getEmailFooterTemplate method
     */
    public function testGetEmailFooterTemplate()
    {
        $storeId = 1;
        $value = 'design_email_footer_template';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_EMAIL_FOOTER_TEMPLATE, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->getEmailFooterTemplate($storeId));
    }
}
