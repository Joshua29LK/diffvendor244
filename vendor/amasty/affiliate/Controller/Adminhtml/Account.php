<?php

namespace Amasty\Affiliate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Result\PageFactory;
use Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Affiliate\Model\AccountFactory;

abstract class Account extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @var Action\Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Account constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param Filter $filter
     * @param AccountFactory $accountFactory
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        Filter $filter,
        AccountFactory $accountFactory,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        CurrencyInterface $currency,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
        $this->coreRegistry = $coreRegistry;
        $this->currency = $currency;
        $this->context = $context;
        $this->storeManager = $storeManager;
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
            ->_addBreadcrumb(__('Manage Affiliate Accounts'), __('Manage Affiliate Accounts'));

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Affiliate::account');
    }
}
