<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\SimpleGoogleShopping\Block\Adminhtml\Feeds;

/**
 * Report block
 */
class Report extends \Magento\Backend\Block\Template
{
    public $licenseHelper = null;
    public $sgsModel = null;
    public $sgsHelper = null;
    public function __construct(
        \Wyomind\SimpleGoogleShopping\Helper\Delegate $wyomind,
        \Magento\Backend\Block\Template\Context $context,
        /** @delegation off */
        array $data = []
    )
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * Get block content
     * @return string
     */
    public function getContent()
    {
        $request = $this->getRequest();
        $id = $request->getParam('simplegoogleshopping_id');
        if ($id != 0) {
            $this->sgsModel->load($id);
            $this->sgsModel->limit = $this->licenseHelper->getStoreConfig('simplegoogleshopping/system/preview');
            $this->sgsModel->setDisplay(false);
            $unserialize = "unserialize";
            return $this->sgsHelper->reportToHtml($unserialize((string) $this->sgsModel->getSimplegoogleshoppingReport()));
        }
        return '';
    }
}