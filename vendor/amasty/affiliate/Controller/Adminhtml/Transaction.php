<?php

namespace Amasty\Affiliate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Affiliate\Model\TransactionFactory;

abstract class Transaction extends Action
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
     * @var \Amasty\Affiliate\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Transaction constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository
     * @param Filter $filter
     * @param TransactionFactory $transactionFactory
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        Filter $filter,
        TransactionFactory $transactionFactory,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->accountRepository = $accountRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->transactionRepository = $transactionRepository;
        $this->transactionFactory = $transactionFactory;
        $this->coreRegistry = $coreRegistry;
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
            ->_addBreadcrumb(__('Manage Affiliate Transactions'), __('Manage Affiliate Transactions'));

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Affiliate::transaction');
    }
}
