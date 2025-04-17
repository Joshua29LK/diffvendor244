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

class Coupon extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $salesRuleCoupon;

    /**
     * Coupon constructor.
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $salesRuleCoupon
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $salesRuleCoupon
    ) {
        $this->salesRuleCoupon = $salesRuleCoupon;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $code[] = [
            'label' => "Custom Coupon Code",
            'value' => 0
        ];
        $list=$this->salesRuleCoupon->create()->load();
        foreach ($list as $key) {
            $code[] = [
                'label' => $key->getCode(),
                'value' => $key->getCode()
            ];
        }
        return $code;
    }
}
