<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


declare(strict_types=1);

namespace Amasty\Rma\ViewModel\Order\History;

use Amasty\Rma\Api\CreateReturnProcessorInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Grid implements ArgumentInterface
{
    /**
     * @var CreateReturnProcessorInterface
     */
    private $createReturnProcessor;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        CreateReturnProcessorInterface $createReturnProcessor,
        OrderCollectionFactory $orderCollectionFactory,
        Session $customerSession
    ) {
        $this->createReturnProcessor = $createReturnProcessor;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
    }

    public function getReturnableOrderIds(): string
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return '';
        }

        $returnableOrderReadIds = [];
        $orderCollection = $this->orderCollectionFactory->create($customerId);

        /** @var Order $order */
        foreach ($orderCollection as $order) {
            $returnOrder = $this->createReturnProcessor->process($order->getId());
            $returnItems = $returnOrder ? $returnOrder->getItems() : [];

            foreach ($returnItems as $returnItem) {
                if ($returnItem->isReturnable()) {
                    $returnableOrderReadIds[] = $order->getRealOrderId() . '-' . $order->getId();
                    continue 2;
                }
            }
        }

        return implode(',', $returnableOrderReadIds);
    }
}
