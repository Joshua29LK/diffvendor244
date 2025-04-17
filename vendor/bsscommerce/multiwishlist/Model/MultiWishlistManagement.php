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
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Model;

/**
 * Class MultiWishlistManagement
 *
 * @package Bss\MultiWishlist\Model
 */
class MultiWishlistManagement implements \Bss\MultiWishlist\Api\MultiWishlistManagementInterface
{

    /**
     * @var \Bss\MultiWishlist\Helper\Data
     */
    protected $helperData;

    /**
     * MultiWishlistManagement constructor.
     *
     * @param \Bss\MultiWishlist\Helper\Data $helperData
     */
    public function __construct(
        \Bss\MultiWishlist\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Get module configs
     *
     * @param int $storeId
     * @return string[]|void
     */
    public function getConfig($storeId)
    {
        $result["module_configs"] = [
            "enable" =>  $this->helperData->isEnable($storeId),
            "remove_item_addcart" => $this->helperData->removeItemAfterAddCart($storeId),
            "redirect" => $this->helperData->isRedirect($storeId)
        ];
        return $result;
    }
}
