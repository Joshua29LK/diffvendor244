<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_EditOrder
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Plugin\Invoice;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\Save as InvoiceSave;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class Save
 * @package Mageplaza\EditOrder\Plugin\Invoice
 */
class Save
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Save constructor.
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData,
        OrderFactory $orderFactory
    ) {
        $this->_request     = $request;
        $this->_helperData  = $helperData;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param InvoiceSave $object
     * @return array
     */
    public function beforeExecute(InvoiceSave $object)
    {
        $orderId = $this->_request->getParam('order_id');

        if ($orderId) {
            $order   = $this->orderFactory->create()->load($orderId);
            if ((int) $order->getMpIsEditOrder() === 1) {
                $data = $object->getRequest()->getParam('invoice');
                $data['capture_case'] = 'offline';
                $object->getRequest()->setPostValue('invoice', $data);
            }
        }

        return [];
    }
}
