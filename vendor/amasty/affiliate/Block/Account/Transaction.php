<?php

namespace Amasty\Affiliate\Block\Account;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Model\PriceConverter;
use Amasty\Affiliate\Model\AccountPricePreparer;
use Amasty\Affiliate\Model\ResourceModel\Transaction\Collection as TransactionCollection;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;
use Amasty\Affiliate\Model\Account;
use Amasty\Affiliate\Model\Source\BalanceChangeType;
use Amasty\Affiliate\Model\Transaction as TransactionModel;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Transaction extends Template
{
    /**
     * @var string
     */
    protected $_template = 'account/transaction.phtml';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var PriceConverter
     */
    private $priceConverter;
    /**
     * @var AccountPricePreparer
     */
    private $accountPricePreparer;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Session $customerSession,
        AccountRepositoryInterface $accountRepository,
        PriceConverter $priceConverter,
        AccountPricePreparer $accountPricePreparer,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $context->getStoreManager();
        $this->accountRepository = $accountRepository;
        $this->priceConverter = $priceConverter;
        $this->accountPricePreparer = $accountPricePreparer;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Balance'));
    }

    /**
     * @return TransactionCollection|null
     */
    public function getTransactions()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return null;
        }

        $accountId = $this->accountRepository->getByCustomerId($customerId)->getAccountId();
        if (!$accountId) {
            return null;
        }

        /** @var TransactionCollection $transactions */
        $transactions = $this->collectionFactory->create();
        $transactions->addAccountIdFilter($accountId);
        $transactions->addFrontTypeFilter();
        $transactions->addCompletedFilter();
        $transactions->removeZeroComissionTransactions();
        $transactions->setPageSize($pageSize);
        $transactions->setCurPage($page);

        return $transactions;
    }

    /**
     * @return TransactionCollection
     */
    public function getAscTransactions()
    {
        return $this->getTransactions()->addAscSorting();
    }

    /**
     * @return TransactionCollection
     */
    public function getDescTransactions()
    {
        return $this->getTransactions()->addDescSorting();
    }

    /**
     * @return Transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getTransactions()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amasty.affiliate.transaction.pager'
            )->setShowPerPage(true)->setCollection(
                $this->getTransactions()
            );
            $this->setChild('pager', $pager);
            $this->getTransactions()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @param float|null $value
     * @return string
     */
    public function convertToPrice($value)
    {
        $value = $this->priceConverter->convertToPrice($value);

        return $value;
    }

    /**
     * @param TransactionModel $transactions
     * @return \Magento\Framework\Phrase
     */
    public function prepareDetails($transactions)
    {
        if ($transactions->getBalanceChangeType() === BalanceChangeType::TYPE_SUBTRACTION
            && $transactions->getType() !== TransactionModel::TYPE_WITHDRAWAL
        ) {
            $details = __('Subtraction after refund per Order #%1', $transactions->getOrderIncrementId());
        } else {
            switch ($transactions->getType()) {
                case TransactionModel::TYPE_PER_SALE:
                    $details = __('Commission per oder #%1', $transactions->getOrderIncrementId());
                    break;
                case TransactionModel::TYPE_WITHDRAWAL:
                    $details = __('Withdrawal');
                    break;
                default:
                    $details = __('Per Profit');
            }
        }

        return $details;
    }

    /**
     * @param TransactionModel $transaction
     * @return string
     */
    public function getPriceClass($transaction)
    {
        $class = 'amasty_affiliate_gain';

        if ($transaction->getBalanceChangeType() == BalanceChangeType::TYPE_SUBTRACTION) {
            $class = 'amasty_affiliate_losses';
        }

        return $class;
    }

    /**
     * @param TransactionModel $transaction
     * @return string
     */
    public function showCharacter($transaction)
    {
        $character = '';

        if ($transaction->getBalanceChangeType() == BalanceChangeType::TYPE_ADDITION) {
            $character = '+';
        }

        return $character;
    }

    /**
     * @return string
     */
    public function getCurrentCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        /** @var Account $account */
        $account = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());
        $this->accountPricePreparer->preparePrices($account);

        return $account;
    }

    /**
     * @param float|null $price
     * @return float|int|string
     */
    public function convertPriceToCurrentCurrency($price)
    {
        return $this->priceConverter->convertPriceToCurrentCurrency($price);
    }
}
