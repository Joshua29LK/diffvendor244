<?php

namespace Amasty\Affiliate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Amasty\Affiliate\Model\ProgramFactory;

abstract class Program extends Action
{
    /**
     * @var \Amasty\Affiliate\Api\ProgramRepositoryInterface
     */
    protected $programRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProgramFactory
     */
    protected $programFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Program constructor.
     * @param Action\Context $context
     * @param \Amasty\Affiliate\Api\ProgramRepositoryInterface $programRepository
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProgramFactory $programFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        \Amasty\Affiliate\Api\ProgramRepositoryInterface $programRepository,
        PageFactory $resultPageFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProgramFactory $programFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->programRepository = $programRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->programFactory = $programFactory;
        $this->_dateFilter = $dateFilter;
        $this->dataPersistor = $dataPersistor;
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
            ->_addBreadcrumb(__('Manage Affiliate Program'), __('Manage Affiliate Program'));

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Affiliate::program');
    }
}
