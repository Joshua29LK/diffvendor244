<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CategoriesImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CategoriesImportExport\Block\Adminhtml\Export\Filter;

use Magento\ImportExport\Model\Export as ExportModel;

/**
 * Class Form
 *
 * @package Bss\CategoriesImportExport\Block\Adminhtml\Export\Filter
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'export_filter_form',
                    'action' => $this->getUrl('*/*/export'),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset('bss_filter_fieldset', ['legend' => __('Entity Attributes')]);
        $fieldset->addField(
            'export-related-skus',
            'select',
            [
                'name' => ExportModel::FILTER_ELEMENT_GROUP . '[export-related-skus]',
                'title' => __('Export Related SKUs'),
                'label' => __('Export Related SKUs'),
                'required' => false,
                'value' => 1,
                'values' => [
                    ["value" => 0, "label" => __("No")],
                    ["value" => 1, "label" => __("Yes")]
                ]
            ]
        );
        $fieldset->addField(
            'export-by',
            'select',
            [
                'name' => ExportModel::FILTER_ELEMENT_GROUP . '[export-by]',
                'title' => __('Export By'),
                'label' => __('Export By'),
                'required' => false,
                'value' => 'all',
                'values' => [
                    ["value" => 'all', "label" => __("All")],
                    ["value" => 'store-id', "label" => "Store ID"],
                    ["value" => 'category-id', "label" => __("Category ID")]
                ]
            ]
        );

        $fieldset->addField(
            'store-id',
            'select',
            [
                'name' => ExportModel::FILTER_ELEMENT_GROUP . '[store-id]',
                'title' => __('Choose Store'),
                'label' => __('Choose Store'),
                'required' => false,
                'value' => 'all',
                'values' => $this->getStoreOptions()
            ]
        );

        $fieldset->addField(
            'category-id',
            'text',
            [
                'name' => ExportModel::FILTER_ELEMENT_GROUP . '[category-id]',
                'title' => __('Input Category Id'),
                'label' => __('Input Category Id'),
                'required' => false,
                'note' => 'Comma-separated.'
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        // define field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Form\Element\Dependence::class
            )->addFieldMap(
                "export-by",
                ExportModel::FILTER_ELEMENT_GROUP . '[export-by]'
            )->addFieldMap(
                "category-id",
                ExportModel::FILTER_ELEMENT_GROUP . '[category-id]'
            )->addFieldMap(
                "store-id",
                ExportModel::FILTER_ELEMENT_GROUP . '[store-id]'
            )->addFieldDependence(
                ExportModel::FILTER_ELEMENT_GROUP . '[category-id]',
                ExportModel::FILTER_ELEMENT_GROUP . '[export-by]',
                'category-id'
            )->addFieldDependence(
                ExportModel::FILTER_ELEMENT_GROUP . '[store-id]',
                ExportModel::FILTER_ELEMENT_GROUP . '[export-by]',
                'store-id'
            )
        );

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    public function getStoreOptions()
    {
        $options = [];
        $options[] = ["value" => "all", "label" => __("All")];
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $options[] = [
                "value" => $store->getStoreId(),
                "label" => $store->getName()
            ];
        }
        return $options;
    }
}
