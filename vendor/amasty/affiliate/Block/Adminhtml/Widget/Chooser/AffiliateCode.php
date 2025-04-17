<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Block\Adminhtml\Widget\Chooser;

use Amasty\Affiliate\Model\Program\Source\IsActive;
use Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;

class AffiliateCode extends Extended
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var IsActive
     */
    private $activeOptions;

    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        CollectionFactory $collectionFactory,
        IsActive $activeOptions,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->collectionFactory = $collectionFactory;
        $this->activeOptions = $activeOptions;
    }

    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('grid_id')) {
            $this->setId($this->getRequest()->getParam('grid_id'));
        } else {
            $this->setId('affiliateCodeChooserGrid_' . $this->getId());
        }

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setDefaultSort('referring_code');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Prepare Affiliate Code Collection for chooser
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->collectionFactory->create());

        return parent::_prepareCollection();
    }

    /**
     * Define Chooser Grid Columns and filters
     *
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_codes',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_codes',
                'values' => $this->getSelectedCodes(),
                'align' => 'center',
                'index' => 'referring_code',
                'use_index' => true
            ]
        );
        $this->addColumn('referring_code', ['header' => __('Referring Code'), 'index' => 'referring_code']);

        $this->addColumn(
            'is_affiliate_active',
            [
                'header' => __('Status'),
                'index' => 'is_affiliate_active',
                'width' => '100',
                'type' => 'options',
                'options' => $this->activeOptions->toArray()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Grid URL getter for ajax mode
     *
     * @return string
     */
    public function getGridUrl(): string
    {
        return $this->getUrl(
            'amasty_affiliate/widget/chooser',
            ['_current' => true, 'grid_id' => $this->getId(), 'collapse' => null]
        );
    }

    /**
     * Get Selected ids param from request
     *
     * @return array
     */
    private function getSelectedCodes(): array
    {
        return $this->getRequest()->getPost('selected', []);
    }
}
