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
namespace Bss\CheckoutSuccessPage\Model\Config\Source;

class Cmslist extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * Cmslist constructor.
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $cms = [];
        $list=$this->blockFactory->create()->getCollection();
        foreach ($list as $key) {
            $cms[] = [
                'label' => $key->getTitle(),
                'value' => $key->getBlockId()
            ];
        }
        return $cms;
    }
}
