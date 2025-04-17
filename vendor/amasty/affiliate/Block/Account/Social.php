<?php

namespace Amasty\Affiliate\Block\Account;

use Magento\Framework\View\Element\Template;

abstract class Social extends \Amasty\Affiliate\Block\SocialButtons\AbstractButtons
{
    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Links\CollectionFactory
     */
    private $linksCollectionFactory;

    /**
     * Refer constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Amasty\Affiliate\Model\ResourceModel\Links\CollectionFactory $linksCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\ResourceModel\Links\CollectionFactory $linksCollectionFactory,
        \Amasty\Affiliate\Model\Account $account,
        array $data = []
    ) {
        $this->linksCollectionFactory = $linksCollectionFactory;
        parent::__construct($context, $customerSession, $accountRepository, $account, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function showConfig()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/friends/on_my_account');
    }

    /**
     * @return array
     */
    public function getLinkTypes()
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Links\Collection $linksCollection */
        $linksCollection = $this->linksCollectionFactory->create();
        $types = $linksCollection->getTypes($this->getAccount()->getAccountId());

        return $types;
    }

    /**
     * @param $linkType
     * @return int
     */
    public function getCountByType($linkType)
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Links\Collection $linksCollection */
        $linksCollection = $this->linksCollectionFactory->create();
        $count = $linksCollection->getCount($this->getAccount()->getAccountId(), $linkType);

        return $count;
    }
}
