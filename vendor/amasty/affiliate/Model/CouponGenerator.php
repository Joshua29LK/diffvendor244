<?php

namespace Amasty\Affiliate\Model;

class CouponGenerator
{
    /**
     * Map keys in old and new services
     *
     * Controller was used as old service
     * @see \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Generate
     *  - key = key in new service
     *  - value = key in old service
     *
     * @var array
     */
    private $keyMap = [
        'quantity' => 'qty'
    ];

    /**
     * @var \Magento\SalesRule\Model\Service\CouponManagementService
     */
    private $couponManagementService;

    /**
     * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory
     */
    private $generationSpecFactory;

    public function __construct(
        \Magento\SalesRule\Model\Service\CouponManagementService $couponManagementService,
        \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory $generationSpecFactory
    ) {
        $this->couponManagementService = $couponManagementService;
        $this->generationSpecFactory = $generationSpecFactory;
    }

    /**
     * @param array $parameters
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateCodes(array $parameters)
    {
        $couponSpecData = $this->convertCouponSpecData($parameters);
        $couponSpec = $this->generationSpecFactory->create(['data' => $couponSpecData]);

        return $this->couponManagementService->generate($couponSpec);
    }

    /**
     * We should map old values to new one
     * We need to do this, as new service with another key names was added
     *
     * @param array $data
     * @return array
     */
    private function convertCouponSpecData(array $data)
    {
        foreach ($this->keyMap as $mapKey => $mapValue) {
            $data[$mapKey] = isset($data[$mapValue]) ? $data[$mapValue] : null;
        }

        return $data;
    }
}
