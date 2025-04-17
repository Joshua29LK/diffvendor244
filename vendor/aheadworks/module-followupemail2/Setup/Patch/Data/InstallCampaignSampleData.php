<?php
namespace Aheadworks\Followupemail2\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Aheadworks\Followupemail2\Model\Sample\Reader\Xml as SampleXmlReader;
use Aheadworks\Followupemail2\Api\Data\CampaignInterface;
use Aheadworks\Followupemail2\Api\Data\CampaignInterfaceFactory;
use Aheadworks\Followupemail2\Api\CampaignRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterface;
use Aheadworks\Followupemail2\Api\Data\EventInterfaceFactory;
use Aheadworks\Followupemail2\Api\EventRepositoryInterface;
use Aheadworks\Followupemail2\Model\Event\LifetimeConditionConverter;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterfaceFactory;
use Aheadworks\Followupemail2\Api\EmailRepositoryInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailContentInterfaceFactory;
use Aheadworks\Followupemail2\Model\Serializer;

/**
 * Class Install campaign data's sample
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallCampaignSampleData implements DataPatchInterface, PatchRevertableInterface, PatchVersionInterface
{
    /**
     * @var SampleXmlReader
     */
    private $sampleXmlReader;

    /**
     * @var CampaignInterfaceFactory
     */
    private $campaignFactory;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var LifetimeConditionConverter
     */
    private $lifetimeConditionConverter;

    /**
     * @var EmailInterfaceFactory
     */
    private $emailFactory;

    /**
     * @var EmailRepositoryInterface
     */
    private $emailRepository;

    /**
     * @var EmailContentInterfaceFactory
     */
    private $emailContentFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param SampleXmlReader $sampleXmlReader
     * @param CampaignInterfaceFactory $campaignFactory
     * @param CampaignRepositoryInterface $campaignRepository
     * @param EventInterfaceFactory $eventFactory
     * @param EventRepositoryInterface $eventRepository
     * @param LifetimeConditionConverter $lifetimeConditionConverter
     * @param EmailInterfaceFactory $emailFactory
     * @param EmailRepositoryInterface $emailRepository
     * @param EmailContentInterfaceFactory $emailContentFactory
     * @param Serializer $serializer
     */
    public function __construct(
        SampleXmlReader $sampleXmlReader,
        CampaignInterfaceFactory $campaignFactory,
        CampaignRepositoryInterface $campaignRepository,
        EventInterfaceFactory $eventFactory,
        EventRepositoryInterface $eventRepository,
        LifetimeConditionConverter $lifetimeConditionConverter,
        EmailInterfaceFactory $emailFactory,
        EmailRepositoryInterface $emailRepository,
        EmailContentInterfaceFactory $emailContentFactory,
        Serializer $serializer
    ) {
        $this->sampleXmlReader = $sampleXmlReader;
        $this->campaignFactory = $campaignFactory;
        $this->campaignRepository = $campaignRepository;
        $this->eventFactory = $eventFactory;
        $this->eventRepository = $eventRepository;
        $this->lifetimeConditionConverter = $lifetimeConditionConverter;
        $this->emailFactory = $emailFactory;
        $this->emailRepository = $emailRepository;
        $this->emailContentFactory = $emailContentFactory;
        $this->serializer = $serializer;
    }

    /**
     * Install campaign sample data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->saveSampleCampaigns();
    }

    /**
     * Remove patch on uninstall command in order to be able to install it again
     */
    public function revert()
    {
        return true;
    }

    /**
     * Save sample campaigns
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveSampleCampaigns()
    {
        $sampleData = $this->sampleXmlReader->read();
        foreach ($sampleData as $campaignData) {
            /** @var CampaignInterface $campaign */
            $campaign = $this->campaignFactory->create();
            $campaign
                ->setName($campaignData[CampaignInterface::NAME])
                ->setDescription($campaignData[CampaignInterface::DESCRIPTION])
                ->setStatus(CampaignInterface::STATUS_ENABLED);
            $campaign = $this->campaignRepository->save($campaign);

            foreach ($campaignData['event'] as $eventData) {
                /** @var EventInterface $event */
                $event = $this->eventFactory->create();
                $event
                    ->setCampaignId($campaign->getId())
                    ->setStoreIds([0])
                    ->setName($eventData[EventInterface::NAME])
                    ->setEventType($eventData[EventInterface::EVENT_TYPE])
                    ->setFailedEmailsMode($eventData[EventInterface::FAILED_EMAILS_MODE])
                    ->setLifetimeConditions(
                        $this->lifetimeConditionConverter->getConditionsSerialized($eventData)
                    )
                    ->setProductTypeIds(['all'])
                    ->setCustomerGroups(['all'])
                    ->setCartConditions($this->serializer->serialize([]))
                    ->setStatus(EventInterface::STATUS_DISABLED);
                if (isset($eventData[EventInterface::ORDER_STATUSES])) {
                    $event->setOrderStatuses(
                        explode(',', $eventData[EventInterface::ORDER_STATUSES] ?? '')
                    );
                } else {
                    $event->setOrderStatuses(['all']);
                }
                $event = $this->eventRepository->save($event);

                $position = 1;
                foreach ($eventData['email'] as $emailData) {
                    /** @var EmailInterface $email */
                    $email = $this->emailFactory->create();
                    /** @var EmailContentInterface $emailContent */
                    $emailContent = $this->emailContentFactory->create();
                    $emailContent
                        ->setSubject($emailData[EmailContentInterface::SUBJECT])
                        ->setContent($emailData[EmailContentInterface::CONTENT])
                        ->setSenderName('')
                        ->setSenderEmail('')
                        ->setHeaderTemplate('')
                        ->setFooterTemplate('');
                    $email
                        ->setEventId($event->getId())
                        ->setName($emailData[EmailInterface::NAME])
                        ->setEmailSendDays($emailData[EmailInterface::EMAIL_SEND_DAYS])
                        ->setEmailSendHours($emailData[EmailInterface::EMAIL_SEND_HOURS])
                        ->setEmailSendMinutes($emailData[EmailInterface::EMAIL_SEND_MINUTES])
                        ->setContent([$emailContent])
                        ->setStatus(EmailInterface::STATUS_ENABLED)
                        ->setPosition($position);
                    $this->emailRepository->save($email);
                    $position++;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }
}
