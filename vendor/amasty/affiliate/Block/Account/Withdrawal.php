<?php

namespace Amasty\Affiliate\Block\Account;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\Data\AccountInterface;
use Amasty\Affiliate\Model\PriceConverter;
use Amasty\Affiliate\Model\ResourceModel\Withdrawal\CollectionFactory;
use Amasty\Affiliate\Model\Url;
use Amasty\Affiliate\Model\AccountPricePreparer;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Withdrawal extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'account/withdrawal.phtml';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Url
     */
    private $urlBuilder;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var AccountPricePreparer
     */
    private $accountPricePreparer;
    /**
     * @var PriceConverter
     */
    private $priceConverter;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        AccountRepositoryInterface $accountRepository,
        Url $urlBuilder,
        Session $customerSession,
        AccountPricePreparer $accountPricePreparer,
        PriceConverter $priceConverter,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->accountRepository = $accountRepository;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->accountPricePreparer = $accountPricePreparer;
        $this->priceConverter = $priceConverter;
        parent::__construct($context, $data);
    }

    public function showCancel($status)
    {
        $showCancel = false;

        $allowedStatuses = [
            \Amasty\Affiliate\Model\Transaction::STATUS_PENDING
        ];

        if (in_array($status, $allowedStatuses)) {
            $showCancel = true;
        }

        return $showCancel;
    }

    /**
     * @return AccountInterface
     */
    public function getAccount()
    {
        /** @var AccountInterface $account */
        $account = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());
        $this->accountPricePreparer->preparePrices($account);

        return $account;
    }

    /**
     * @return mixed
     */
    public function getMinimumAmount()
    {
        $amount = $this->_scopeConfig->getValue('amasty_affiliate/withdrawal/minimum_amount');

        return $amount;
    }

    /**
     * @return mixed|string
     */
    public function getMinimumPriceAmount()
    {
        $amount = $this->getMinimumAmount();
        $amount = $this->convertToPrice($amount);

        return $amount;
    }

    public function getMinimumBalance()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/withdrawal/minimum_balance');
    }

    public function getMinimumBalancePrice()
    {
        return $this->convertToPrice($this->getMinimumBalance());
    }

    /**
     * @return \Amasty\Affiliate\Model\ResourceModel\Withdrawal\Collection|bool
     */
    public function getWithdrawals()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;

        /** @var \Amasty\Affiliate\Model\ResourceModel\Withdrawal\Collection $collection */
        $collection = $this->collectionFactory->create();
        $accountId = $this->getAccount()->getAccountId();
        if (!$accountId) {
            return false;
        }

        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        $collection->addAccountIdFilter($accountId);

        return $collection;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param \Amasty\Affiliate\Model\Withdrawal $withdrawal
     * @return string
     */
    public function getCancelUrl($withdrawal)
    {
        $id = $withdrawal->getTransactionId();
        $url = $this->urlBuilder->getUrl(
            $this->urlBuilder->getPath('account_withdrawal/cancel'),
            ['withdrawal_id' => $id]
        );

        return $url;
    }

    /**
     * @param \Amasty\Affiliate\Model\Withdrawal $withdrawal
     * @return string
     */
    public function getRepeatUrl($withdrawal)
    {
        $id = $withdrawal->getTransactionId();
        $url = $this->urlBuilder->getUrl(
            $this->urlBuilder->getPath('account_withdrawal/repeat'),
            ['withdrawal_id' => $id]
        );

        return $url;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getWithdrawals()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amasty.affiliate.withdrawal.pager'
            )->setShowPerPage(true)->setCollection(
                $this->getWithdrawals()
            );
            $this->setChild('pager', $pager);
            $this->getWithdrawals()->load();
        }
        return $this;
    }

    /**
     * @param $value
     * @return string
     */
    public function convertToPrice($value)
    {
        $value = $this->priceConverter->convertToPrice($value);

        return $value;
    }
}
