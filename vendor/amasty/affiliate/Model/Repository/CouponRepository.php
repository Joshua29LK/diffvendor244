<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Api\CouponRepositoryInterface;
use Amasty\Affiliate\Api\Data\CouponInterface;
use Amasty\Affiliate\Api\Data\CouponInterfaceFactory;
use Amasty\Affiliate\Model\ResourceModel\Coupon as CouponResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class CouponRepository extends AbstractRepository implements CouponRepositoryInterface
{
    /**
     * @var CouponResource
     */
    private $resource;

    /**
     * @var CouponInterfaceFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $couponById = [];

    public function __construct(
        CouponResource $resource,
        CouponInterfaceFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param CouponInterface $coupon
     * @return CouponInterface
     * @throws CouldNotSaveException
     */
    public function save(CouponInterface $coupon)
    {
        if ($coupon->getId()) {
            /** @var \Amasty\Affiliate\Model\Banner $entity */
            $coupon = $this->get($coupon->getId())->addData($coupon->getData());
        }
        try {
            $this->resource->save($coupon);
        } catch (\Exception $e) {
            if ($coupon->getId()) {
                throw new CouldNotSaveException(
                    __('Unable to save coupon with ID %1. Error: %2', [$coupon->getId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new coupon. Error: %1', $e->getMessage()));
        }

        return $coupon;
    }

    /**
     * @param int $couponId
     * @return CouponInterface|CouponResource
     * @throws NoSuchEntityException
     */
    public function get($couponId)
    {
        if (!isset($this->couponById[$couponId])) {
            /** @var \Amasty\Affiliate\Model\Coupon $entity */
            $coupon = $this->factory->create();
            $this->resource->load($coupon, $couponId, CouponInterface::ENTITY_ID);
            if (!$coupon->getId()) {
                throw new NoSuchEntityException(__('Coupon with specified ID "%1" not found.', $couponId));
            }
            $this->couponById[$couponId] = $coupon;
        }
        return $this->couponById[$couponId];
    }

    /**
     * @param CouponInterface $coupon
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CouponInterface $coupon)
    {
        try {
            $this->resource->delete($coupon);
            unset($this->couponById[$coupon->getId()]);
        } catch (\Exception $e) {
            if ($coupon->getId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove coupon with ID %1. Error: %2', [$coupon->getId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove coupon. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
