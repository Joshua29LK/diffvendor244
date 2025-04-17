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
 * @package    Bss_CheckoutSuccessPage
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutSuccessPage\Block;

use Bss\CheckoutSuccessPage\Helper\Data;

class Cms extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string $_template = "Bss_CheckoutSuccessPage::checkout/cms.phtml"
     */
    protected $_template = "Bss_CheckoutSuccessPage::checkout/cms.phtml";

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * Cms constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $helper
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $context->getStoreManager();
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function content()
    {
        $blockId = $this->helper->getConfigValue('checkoutsuccesspage/cms/cms1');
        $html = '';
        if ($blockId) {
            $storeId = $this->storeManager->getStore()->getId();
            $block = $this->blockFactory->create();
            $block->setStoreId($storeId)->load($blockId);

            $html = $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
        }
        return $html;
    }
}
