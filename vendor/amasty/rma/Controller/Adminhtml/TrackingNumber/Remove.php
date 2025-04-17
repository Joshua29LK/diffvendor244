<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Adminhtml\TrackingNumber;

use Amasty\Rma\Api\RequestRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Remove extends \Magento\Backend\App\Action
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    public function __construct(
        RequestRepositoryInterface $requestRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
    }

    public function execute()
    {
        /** @var Json $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $trackingId = $this->getRequest()->getParam('id');
        if (($hash = $this->getRequest()->getParam('hash')) && $trackingId) {
            try {
                $this->requestRepository->deleteTrackingById($trackingId);
            } catch (\Exception $e) {
                return $response->setData([]);
            }

            return $response->setData(['success' => true]);
        }

        return $response->setData([]);
    }
}
