<?php

namespace Amasty\Affiliate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Amasty\Affiliate\Model\ResourceModel\Withdrawal\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Affiliate\Model\WithdrawalFactory;

abstract class Withdrawal extends Action
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
     * @var \Amasty\Affiliate\Api\WithdrawalRepositoryInterface
     */
    protected $withdrawalRepository;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var WithdrawalFactory
     */
    protected $withdrawalFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Withdrawal constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param \Amasty\Affiliate\Api\WithdrawalRepositoryInterface $withdrawalRepository
     * @param Filter $filter
     * @param WithdrawalFactory $withdrawalFactory
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Amasty\Affiliate\Api\WithdrawalRepositoryInterface $withdrawalRepository,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        Filter $filter,
        WithdrawalFactory $withdrawalFactory,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->accountRepository = $accountRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->withdrawalRepository = $withdrawalRepository;
        $this->withdrawalFactory = $withdrawalFactory;
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
            ->_addBreadcrumb(__('Manage Affiliate Withdrawals'), __('Manage Affiliate Withdrawals'));

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Affiliate::withdrawals');
    }
}
