<?php

namespace Amasty\Affiliate\Controller\Adminhtml\Account;

use Amasty\Affiliate\Api\Data\CouponInterface;
use Amasty\Affiliate\Model\CouponCreator;
use Amasty\Affiliate\Model\CouponListProvider;
use Amasty\Affiliate\Model\Repository\ProgramRepository;
use Amasty\Affiliate\Api\CouponRepositoryInterface as AmastyCouponRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class SaveCustomCoupons extends Action
{
    /**
     * @var ProgramRepository
     */
    private $programRepository;

    /**
     * @var CouponCreator
     */
    private $couponCreator;

    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var AmastyCouponRepositoryInterface
     */
    private $amastyCouponRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CouponListProvider
     */
    private $couponListProvider;

    public function __construct(
        Context $context,
        ProgramRepository $programRepository,
        CouponCreator $couponCreator,
        CouponRepositoryInterface $couponRepository,
        AmastyCouponRepositoryInterface $amastyCouponRepository,
        LoggerInterface $logger,
        CouponListProvider $couponListProvider
    ) {
        $this->programRepository = $programRepository;
        $this->couponCreator = $couponCreator;
        $this->couponRepository = $couponRepository;
        $this->amastyCouponRepository = $amastyCouponRepository;
        $this->logger = $logger;
        $this->couponListProvider = $couponListProvider;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON);
        }

        try {
            $generateCoupons = [];
            $customCoupons = $this->getRequest()->getParams();
            $systemCouponId = $customCoupons['rowId'];
            $systemCoupon = $this->amastyCouponRepository->get($systemCouponId);
            $programId = $systemCoupon->getProgramId();
            $accountId = $systemCoupon->getAccountId();

            $customCouponsCollection = $this->getCouponCollection($accountId, $programId);
            if (empty($customCoupons['data'])) {
                foreach ($customCouponsCollection as $item) {
                    $this->couponRepository->deleteById($item->getCouponId());
                    $this->amastyCouponRepository->delete($item);
                }
            } else {
                $this->editCoupons($customCouponsCollection, $customCoupons);
                $this->deleteCoupons($customCouponsCollection, $customCoupons);

                $program = $this->programRepository->get($programId);
                foreach ($customCoupons['data'] as $customCoupon) {
                    if (empty($customCoupon['entity_id'])) {
                        if (empty(trim($customCoupon['code']))) {
                            throw new NotFoundException(__('One of the fields is empty.'));
                        }
                        $customCoupon['code'] = strtoupper(
                            trim(preg_replace('/\s+/', '', $customCoupon['code']))
                        );
                        $generateCoupons[] = $customCoupon['code'];
                    }
                }
                $this->couponCreator->generateCustomCoupons(
                    $program,
                    $accountId,
                    false,
                    $generateCoupons
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['status' => 'done']);
    }

    /**
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $customCouponsCollection
     * @param array $customCoupons
     */
    private function deleteCoupons($customCouponsCollection, $customCoupons)
    {
        /** @var CouponInterface $item */
        foreach ($customCouponsCollection as $item) {
            $deleteItem = $this->searchCustomCouponsForDelete($customCoupons, $item);
            if ($deleteItem) {
                $this->couponRepository->deleteById($item->getCouponId());
                $this->amastyCouponRepository->delete($item);
            }
        }
    }

    /**
     * @param \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $customCouponsCollection
     * @param array $customCoupons
     */
    public function editCoupons($customCouponsCollection, $customCoupons)
    {
        /** @var CouponInterface $item */
        foreach ($customCouponsCollection as $item) {
            $editCouponCode = $this->searchCustomCouponsForEdit($customCoupons, $item);
            if (!empty($editCouponCode)) {
                $coupon = $this->couponRepository->getById($item->getCouponId());
                $editCouponCode = strtoupper(trim(preg_replace('/\s+/', '', $editCouponCode)));
                $coupon->setCode($editCouponCode);
                $this->couponRepository->save($coupon);
            }
        }
    }

    /**
     * @param array $customCoupons
     * @param CouponInterface $item
     * @return bool
     */
    private function searchCustomCouponsForDelete($customCoupons, $item)
    {
        foreach ($customCoupons['data'] as $customCoupon) {
            if ($customCoupon['code'] == $item->getCode()
                || (isset($customCoupon['coupon_id']) && $customCoupon['coupon_id'] == $item->getCouponId())
                && !empty($customCoupon['entity_id'])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $customCoupons
     * @param CouponInterface $item
     * @return string
     */
    private function searchCustomCouponsForEdit($customCoupons, $item)
    {
        foreach ($customCoupons['data'] as $customCoupon) {
            if (isset($customCoupon['coupon_id']) && $customCoupon['coupon_id'] == $item->getCouponId()
                && !empty($customCoupon['entity_id'])
            ) {
                return $customCoupon['code'];
            }
        }

        return '';
    }

    /**
     * @param int $accountId
     * @param int $programId
     * @return \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection
     */
    private function getCouponCollection($accountId, $programId)
    {
        return $this->couponListProvider->getCustomCouponCollection($accountId, $programId);
    }
}
