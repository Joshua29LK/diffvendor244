<?php

namespace Amasty\Affiliate\Block\Account;

use Amasty\Affiliate\Model\Program\Source\RuleDiscountType;

class Program extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'account/program.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Amasty\Affiliate\Model\Transaction
     */
    protected $transaction;

    /**
     * @var RuleDiscountType
     */
    private $ruleDiscountTypeSource;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Affiliate\Model\Transaction $transaction,
        RuleDiscountType $ruleDiscountTypeSource,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->transaction = $transaction;
        $this->ruleDiscountTypeSource = $ruleDiscountTypeSource;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Affiliate Programs'));
    }

    /**
     * @return \Amasty\Affiliate\Model\ResourceModel\Program\Collection|bool
     */
    public function getPrograms()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;

        if (!$this->customerSession->getCustomerId()) {
            return false;
        }
        $programs = $this->collectionFactory->create();
        $programs->addActiveFilter();
        $programs->setPageSize($pageSize);
        $programs->setCurPage($page);
        $programs->addCustomerAndGroupFilter(
            $this->customerSession->getCustomerId(),
            $this->customerSession->getCustomerGroupId()
        );

        return $programs;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getPrograms()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amasty.affiliate.program.pager'
            )->setShowPerPage(true)->setCollection(
                $this->getPrograms()
            );
            $this->setChild('pager', $pager);
            $this->getPrograms()->load();
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
     * @param string $type
     * @return string
     */
    public function prepareWithdrawalType($type)
    {
        $availableTypes = $this->transaction->getAvailableTypes();
        $type = $availableTypes[$type];

        return $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public function prepareDiscountType($type)
    {
        $availableTypes = $this->ruleDiscountTypeSource->toArray();
        $type = $availableTypes[$type];

        return $type;
    }
}
