<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Plugin\Customer\Model\Plugin;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Data\Form\FormKey as DataFormKey;
use Magento\PageCache\Observer\FlushFormKey;

/**
 * Class FixCustomerFlushFormKey
 *
 * @package Bss\MultiWishlist\Plugin\Customer\Model\Plugin
 */
class FixCustomerFlushFormKey
{
    /**
     * @var SessionFactory
     */
    private $session;

    /**
     * @var DataFormKey
     */
    private $dataFormKey;

    /**
     * FixCustomerFlushFormKey constructor.
     * @param SessionFactory $session
     * @param DataFormKey $dataFormKey
     */
    public function __construct(SessionFactory $session, DataFormKey $dataFormKey)
    {
        $this->session = $session;
        $this->dataFormKey = $dataFormKey;
    }

    /**
     * Set before request Params to customer session
     *
     * @param FlushFormKey $subject
     * @param callable $proceed
     * @param mixed $args
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(FlushFormKey $subject, callable $proceed, ...$args)
    {
        $session = $this->session->create();
        $currentFormKey = $this->dataFormKey->getFormKey();
        $proceed(...$args);
        $beforeParams = $session->getBeforeRequestParams();
        if (isset($beforeParams['form_key']) && $beforeParams['form_key'] == $currentFormKey) {
            $beforeParams['form_key'] = $this->dataFormKey->getFormKey();
            $session->setBeforeRequestParams($beforeParams);
        }
    }
}
