<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\BannerInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\UrlInterface;

class Banner extends \Magento\Framework\Model\AbstractModel implements BannerInterface
{
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    public const TYPE_IMAGE = 'image';
    public const TYPE_TEXT = 'text';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Affiliate\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceModel\Links\CollectionFactory
     */
    private $linksCollectionFactory;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager,
        \Amasty\Affiliate\Model\Account $account,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Model\ResourceModel\Links\CollectionFactory $linksCollectionFactory,
        \Magento\Catalog\Model\ImageUploader $imageUploader,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Magento\Framework\Url $urlBuilder,
        Session $customerSession,
        AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->account = $account;
        $this->scopeConfig = $scopeConfig;
        $this->linksCollectionFactory = $linksCollectionFactory;
        $this->imageUploader = $imageUploader;
        $this->accountRepository = $accountRepository;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Affiliate\Model\ResourceModel\Banner::class);
        $this->setIdFieldName('banner_id');
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        parent::afterSave();

        $image = $this->getImage();

        if ($image !== null) {
            try {
                $this->imageUploader->moveFileFromTmp($image);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        return $this;
    }

    /**
     * @param null $accountId
     * @return int
     */
    public function getClickCount($accountId = null)
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Links\Collection $collection */
        $collection = $this->linksCollectionFactory->create();

        $count = $collection->getCount($accountId, \Amasty\Affiliate\Model\Links::TYPE_BANNER, $this->getBannerId());

        return $count;
    }

    public function getCurrentAccountClickCount()
    {
        return $this->getClickCount(
            $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId())->getAccountId()
        );
    }

    /**
     * @return string|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageUrl()
    {
        $image = $this->getImage();
        if (!$image) {
            return false;
        }
        if (!is_string($image)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while getting the image url.')
            );
        }

        return $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        ) . 'amasty_affiliate/banner/' . $image;
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_IMAGE => __('Image'),
            self::TYPE_TEXT => __('Text')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBannerId()
    {
        return $this->_getData(BannerInterface::BANNER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setBannerId($bannerId)
    {
        $this->setData(BannerInterface::BANNER_ID, $bannerId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_getData(BannerInterface::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->setData(BannerInterface::TITLE, $title);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_getData(BannerInterface::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->setData(BannerInterface::TYPE, $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return $this->_getData(BannerInterface::IMAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setImage($image)
    {
        $this->setData(BannerInterface::IMAGE, $image);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        return $this->_getData(BannerInterface::TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function setText($text)
    {
        $this->setData(BannerInterface::TEXT, $text);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->_getData(BannerInterface::LINK);
    }

    /**
     * {@inheritdoc}
     */
    public function setLink($link)
    {
        $this->setData(BannerInterface::LINK, $link);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelNoFollow()
    {
        return $this->_getData(BannerInterface::REL_NO_FOLLOW);
    }

    /**
     * {@inheritdoc}
     */
    public function setRelNoFollow($relNoFollow)
    {
        $this->setData(BannerInterface::REL_NO_FOLLOW, $relNoFollow);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_getData(BannerInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->setData(BannerInterface::STATUS, $status);

        return $this;
    }
}
