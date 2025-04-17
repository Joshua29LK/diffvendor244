<?php

namespace Amasty\Affiliate\Model\ResourceModel\Report\Bestsellers;

use Amasty\Affiliate\Model\Account;
use Magento\Customer\Model\Session;

class Collection extends \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\ResourceModel\Report $resource
     * @param Account $account
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Report $resource,
        Account $account,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        Session $customerSession,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->account = $account;
        $this->accountRepository = $accountRepository;
        $this->customerSession = $customerSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeLoad()
    {
        $this->_ratingLimit = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId())
            ->getWidgetProductsNum();

        parent::_beforeLoad();

        return $this;
    }
}
