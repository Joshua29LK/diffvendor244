<?php

namespace Amasty\Affiliate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Amasty\Affiliate\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\Affiliate\Model\BannerFactory;

abstract class Banner extends Action
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
     * @var \Amasty\Affiliate\Api\BannerRepositoryInterface
     */
    protected $bannerRepository;

    /**
     * @var BannerFactory
     */
    protected $bannerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Banner constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param \Amasty\Affiliate\Api\BannerRepositoryInterface $bannerRepository
     * @param Filter $filter
     * @param BannerFactory $bannerFactory
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        \Amasty\Affiliate\Api\BannerRepositoryInterface $bannerRepository,
        Filter $filter,
        BannerFactory $bannerFactory,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->bannerRepository = $bannerRepository;
        $this->bannerFactory = $bannerFactory;
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
            ->_addBreadcrumb(__('Manage Affiliate Banners'), __('Manage Affiliate Banners'));

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Affiliate::banner');
    }
}
