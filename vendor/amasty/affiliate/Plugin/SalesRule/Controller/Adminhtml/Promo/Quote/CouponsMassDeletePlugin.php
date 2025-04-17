<?php

namespace Amasty\Affiliate\Plugin\SalesRule\Controller\Adminhtml\Promo\Quote;

use Amasty\Affiliate\Model\Repository\ProgramRepository;
use Amasty\Affiliate\Model\ResourceModel\Coupon;
use Magento\Framework\Message\ManagerInterface;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\CouponsMassDelete;
use Magento\SalesRule\Model\CouponRepository;

/**
 * Decline deleting coupon if he linked in program
 */
class CouponsMassDeletePlugin
{
    /**
     * @var Coupon
     */
    private $resourceCoupon;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var CouponRepository
     */
    private $couponRepository;

    /**
     * @var ProgramRepository
     */
    private $programRepository;

    public function __construct(
        Coupon $resourceCoupon,
        ManagerInterface $messageManager,
        CouponRepository $couponRepository,
        ProgramRepository $programRepository
    ) {
        $this->resourceCoupon = $resourceCoupon;
        $this->messageManager = $messageManager;
        $this->couponRepository = $couponRepository;
        $this->programRepository = $programRepository;
    }

    /**
     * @param CouponsMassDelete $subject
     * @param callable $proceed
     *
     * @return void
     */
    public function aroundExecute(
        CouponsMassDelete $subject,
        callable $proceed
    ) {
        $callParent = true;
        $codesIds = $subject->getRequest()->getParam('ids');
        $codesWithProgram = [];
        $programs = [];

        foreach ($codesIds as $codeId) {
            $coupon = $this->couponRepository->getById($codeId);
            $program = $this->resourceCoupon->getProgramId($coupon->getCode());

            if ($program) {
                $callParent = false;
                $codesWithProgram[] = $coupon->getCode();
                $program = $this->programRepository->get($program);
                $programs[] = $program->getName();
            }
        }

        if ($callParent) {
            return $proceed();
        }

        $this->messageManager->addErrorMessage(
            __(
                'Coupon delete cancelled: coupons (%1) are assigned to Affiliate programs (%2).
                Please unassign this Cart Price Rule from these Affiliate programs and try again.',
                implode(', ', $codesWithProgram),
                implode(', ', $programs)
            )
        );
    }
}
