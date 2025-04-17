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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Controller\Alsoview;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Bss\CABAV\Controller\Product
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create(ResultFactory::TYPE_JSON);
        $params = $this->getRequest()->getParams();
        if (isset($params['id'])) {
            $customerSession = false;
            $productId = $params['id'];
            $customerId = $this->helper->getCustomerId();
            if ($customerId) {
                $customerSession = $customerId;
            } else {
                $ipAddress = $this->helper->getIpAddress();
                if ($ipAddress) {
                    $customerSession = $ipAddress;
                }
            }
            if ($customerSession) {
                $this->resourceConnection->insertProductAlsoView($productId, $customerSession);
                return $resultJson->setData(['message' => __('success')]);
            }
        }
        return $resultJson->setData(['message' => __('error')]);
    }
}