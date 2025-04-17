<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Model\Request;

use Amasty\Rma\Api\CustomerRequestRepositoryInterface;
use Amasty\Rma\Api\GuestCreateRequestProcessInterface;
use Amasty\Rma\Api\StatusRepositoryInterface;
use Amasty\Rma\Model\OptionSource\State;
use Amasty\Rma\Observer\RmaEventNames;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerRequestRepository implements CustomerRequestRepositoryInterface
{
    /**
     * @var \Amasty\Rma\Api\RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var \Amasty\Rma\Api\CreateReturnProcessorInterface
     */
    private $createReturnProcessor;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var GuestCreateRequestProcessInterface
     */
    private $guestCreateRequestProcess;

    /**
     * @var ResourceModel\Request
     */
    private $requestResource;

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    public function __construct(
        \Amasty\Rma\Api\RequestRepositoryInterface $requestRepository,
        \Amasty\Rma\Model\Request\ResourceModel\Request $requestResource,
        \Amasty\Rma\Api\GuestCreateRequestProcessInterface $guestCreateRequestProcess,
        \Amasty\Rma\Api\StatusRepositoryInterface $statusRepository,
        \Amasty\Rma\Api\CreateReturnProcessorInterface $createReturnProcessor,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->requestRepository = $requestRepository;
        $this->createReturnProcessor = $createReturnProcessor;
        $this->eventManager = $eventManager;
        $this->guestCreateRequestProcess = $guestCreateRequestProcess;
        $this->requestResource = $requestResource;
        $this->statusRepository = $statusRepository;
    }

    /**
     * @inheritDoc
     */
    public function create(\Amasty\Rma\Api\Data\RequestInterface $request, $secretKey = '')
    {
        if (!($returnOrder = $this->createReturnProcessor->process($request->getOrderId()))) {
            throw new CouldNotSaveException(__('Wrong Order.'));
        }

        if ($secretKey) {
            if ($orderId = $this->guestCreateRequestProcess->getOrderIdBySecretKey($secretKey)) {
                if ((int)$orderId !== (int)$returnOrder->getOrder()->getEntityId()) {
                    throw new CouldNotSaveException(__('Wrong Order'));
                }
            } else {
                throw new CouldNotSaveException(__('Order not found'));
            }
        } elseif ($returnOrder->getOrder()->getCustomerId() != $request->getCustomerId()) {
            throw new CouldNotSaveException(__('Wrong Customer Id'));
        }

        $requestItems = $request->getRequestItems();
        $returnOrderItems = $returnOrder->getItems();
        $resultItems = [];
        foreach ($requestItems as $requestItem) {
            $item = false;
            foreach ($returnOrderItems as $returnOrderItem) {
                if ($returnOrderItem->getItem()->getItemId() == $requestItem->getOrderItemId()) {
                    $item = $returnOrderItem;
                    break;
                }
            }

            if ($item && $item->isReturnable() && $requestItem->getQty() <= $item->getAvailableQty()
                && isset($item->getResolutions()[$requestItem->getResolutionId()])
            ) {
                $requestItem->setRequestQty($requestItem->getQty());
                $resultItems[] = $requestItem;
            }
        }
        if (empty($resultItems)) {
            throw new CouldNotSaveException(__('Items were not selected'));
        }
        $request->setRequestItems($resultItems);

        $this->eventManager->dispatch(RmaEventNames::BEFORE_CREATE_RMA_BY_CUSTOMER, ['request' => $request]);
        $this->requestRepository->save($request);
        $this->eventManager->dispatch(RmaEventNames::RMA_CREATED_BY_CUSTOMER, ['request' => $request]);

        return $request;
    }

    /**
     * @inheritDoc
     */
    public function getById($requestId, $customerId)
    {
        $request = $this->requestRepository->getById((int)$requestId);
        if ($request->getCustomerId() !== (int)$customerId) {
            throw new NoSuchEntityException(__('Request doesn\'t exsists'));
        }

        return $request;
    }

    /**
     * @inheritDoc
     */
    public function getByHash($hash)
    {
        if (!($requestId = $this->requestResource->getRequestIdByHash($hash))) {
            throw new NoSuchEntityException(__('Request doesn\'t exsists'));
        }
        $request = $this->requestRepository->getById((int)$requestId);

        return $request;
    }

    public function closeRequest($requestIdHash, $customerId = 0)
    {
        if (is_string($requestIdHash)) {
            $request = $this->getByHash($requestIdHash);
        } else {
            $request = $this->getById($requestIdHash, $customerId);
        }

        if ($request) {
            $this->eventManager->dispatch(
                \Amasty\Rma\Observer\RmaEventNames::RMA_CANCELED,
                ['request' => $request]
            );

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function saveTracking($hash, \Amasty\Rma\Api\Data\TrackingInterface $tracking)
    {
        $request = $this->getByHash($hash);

        if ($request) {
            $tracking->setRequestId($request->getRequestId())
                ->setIsCustomer(true);
            $this->requestRepository->saveTracking($tracking);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function removeTracking($hash, $trackingId)
    {
        $request = $this->getByHash($hash);

        if ($request) {

            $tracking = $this->requestRepository->getTrackingById($trackingId);
            if ($tracking->getRequestId() === $request->getRequestId() && $tracking->isCustomer()) {
                $this->requestRepository->deleteTrackingById($trackingId);

                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function saveRating($hash, $rating, $ratingComment)
    {
        if ($rating && $rating > 0 && $rating < 6) {
            try {
                $request = $this->getByHash($hash);
                $status = $this->statusRepository->getById($request->getStatus());

                if (!$request->getRating() && $status->getState() === State::RESOLVED) {
                    $request->setRating($rating)
                        ->setRatingComment($ratingComment);
                    $this->requestRepository->save($request);

                    return true;
                }
            } catch (\Exception $e) {
                null;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getEmptyRequestModel()
    {
        return $this->requestRepository->getEmptyRequestModel();
    }

    /**
     * @inheritDoc
     */
    public function getEmptyRequestItemModel()
    {
        return $this->requestRepository->getEmptyRequestItemModel();
    }

    /**
     * @inheritDoc
     */
    public function getEmptyTrackingModel()
    {
        return $this->requestRepository->getEmptyTrackingModel();
    }
}
