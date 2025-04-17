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
 * @package    Bss_CABAV
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CABAV\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Data constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnable($path)
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool|mixed
     */
    public function getCustomerId()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        if ($customerId != 0) {
            return $customerId;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->_remoteAddress->getRemoteAddress();
    }
}
