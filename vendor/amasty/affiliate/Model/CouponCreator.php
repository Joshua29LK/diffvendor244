<?php

namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\CouponRepositoryInterface as AmastyCouponRepositoryInterface;
use Amasty\Affiliate\Api\Data\CouponInterface;
use Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory;
use Amasty\Affiliate\Model\CouponFactory as AmasyCouponFactory;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Helper\Coupon as CouponHelper;
use Magento\SalesRule\Model\Coupon\Codegenerator;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as SalesCouponCollectionFactory;
use Magento\SalesRule\Api\Data\CouponInterface as SalesCouponInterface;

class CouponCreator
{
    /**
     * @var ResourceModel\Program\CollectionFactory
     */
    private $programCollectionFactory;

    /**
     * @var ResourceModel\Coupon\CollectionFactory
     */
    private $affiliateCouponCollectionFactory;

    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var CouponGenerator
     */
    private $couponGenerator;

    /**
     * @var CouponHelper
     */
    private $couponHelper;

    /**
     * @var CouponFactory
     */
    private $couponFactory;

    /**
     * @var AmasyCouponFactory
     */
    private $amastyCouponFactory;

    /**
     * @var AmastyCouponRepositoryInterface
     */
    private $amastyCouponRepository;

    /**
     * @var Codegenerator
     */
    private $codeGenerator;

    /**
     * @var SalesCouponCollectionFactory
     */
    private $salesCouponCollectionFactory;

    public function __construct(
        CollectionFactory $programCollectionFactory,
        ResourceModel\Coupon\CollectionFactory $affiliateCouponCollectionFactory,
        CouponRepositoryInterface $couponRepository,
        CouponGenerator $couponGenerator,
        CouponHelper $couponHelper,
        CouponFactory $couponFactory,
        AmasyCouponFactory $amastyCouponFactory,
        AmastyCouponRepositoryInterface $amastyCouponRepository,
        Codegenerator $codeGenerator,
        SalesCouponCollectionFactory $salesCouponCollectionFactory
    ) {
        $this->programCollectionFactory = $programCollectionFactory;
        $this->affiliateCouponCollectionFactory = $affiliateCouponCollectionFactory;
        $this->couponRepository = $couponRepository;
        $this->couponGenerator = $couponGenerator;
        $this->couponHelper = $couponHelper;
        $this->couponFactory = $couponFactory;
        $this->amastyCouponFactory = $amastyCouponFactory;
        $this->amastyCouponRepository = $amastyCouponRepository;
        $this->codeGenerator = $codeGenerator;
        $this->salesCouponCollectionFactory = $salesCouponCollectionFactory;
    }

    /**
     * @param $accountId
     * @return $this
     */
    public function addCoupon($accountId)
    {
        /** @var ResourceModel\Program\Collection $programCollection */
        $programCollection = $this->programCollectionFactory->create();
        /** @var Program $program */
        foreach ($programCollection as $program) {
            $this->generateCoupons($program, $accountId);
        }

        return $this;
    }

    /**
     * @param Program $program
     * @param int $accountId
     * @param bool $isSystem
     * @param array $customCodes
     */
    public function generateCustomCoupons(Program $program, int $accountId, bool $isSystem, array $customCodes): void
    {
        foreach ($customCodes as $customCode) {
            $coupon = $this->couponFactory->create();
            $coupon = $this->initSalesCoupon($program, $coupon, $customCode);
            $this->initAmastyCoupon($coupon, $accountId, $program, $isSystem);
        }
    }

    /**
     * @param Program $program
     * @param int $accountId
     */
    public function generateCoupons(Program $program, int $accountId): void
    {
        $affiliateCouponCollection = $this->affiliateCouponCollectionFactory
            ->create()->addFilterForCoupon($program->getProgramId(), $accountId);

        if ($affiliateCouponCollection->getSize() > 0) {
            foreach ($affiliateCouponCollection->getData() as $affiliateCouponData) {
                $coupon = $this->couponRepository->getById($affiliateCouponData['coupon_id']);
                $coupon->setRuleId($program->getRuleId());
                $this->couponRepository->save($coupon);
            }

            return;
        }
        $generatedCodes = $this->couponGenerator->generateCodes(
            $this->getCouponGenerateData($program->getRuleId())
        );

        if ($generatedCodes) {
            $coupon = $this->salesCouponCollectionFactory
                ->create()
                ->addFieldToFilter('code', array_shift($generatedCodes))
                ->getFirstItem();
        } else {
            $coupon = $this->couponFactory->create();
            $coupon = $this->initSalesCoupon($program, $coupon);
        }
        $this->initAmastyCoupon($coupon, $accountId, $program);
    }

    /**
     * @param Program $program
     * @param SalesCouponInterface $coupon
     * @param string $customCode
     *
     * @return CouponInterface
     */
    private function initSalesCoupon(
        Program $program,
        SalesCouponInterface $coupon,
        string $customCode = ''
    ): SalesCouponInterface {
        $code = empty($customCode) ? $this->codeGenerator->generateCode() : $customCode;
        $coupon->setId(null)
            ->setRuleId($program->getRuleId())
            ->setType(CouponHelper::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
            ->setCode($code);

        return $this->couponRepository->save($coupon);
    }

    /**
     * @param SalesCouponInterface $coupon
     * @param int $accountId
     * @param Program $program
     * @param bool $isSystem
     */
    private function initAmastyCoupon(
        SalesCouponInterface $coupon,
        int $accountId,
        Program $program,
        bool $isSystem = true
    ) {
        $data = [
            'account_id' => $accountId,
            'program_id' => $program->getProgramId(),
            'coupon_id' => $coupon->getCouponId(),
            'is_system' => $isSystem
        ];

        $amastyCouponModel = $this->amastyCouponFactory->create();
        $amastyCouponModel->setData($data);
        $this->amastyCouponRepository->save($amastyCouponModel);
    }

    /**
     * @param $ruleId
     * @return array
     */
    private function getCouponGenerateData($ruleId)
    {
        $defaultFormat = $this->couponHelper->getDefaultFormat();
        if ($defaultFormat == 1) {
            $defaultFormat = CouponHelper::COUPON_FORMAT_ALPHANUMERIC;
        }

        return [
            'rule_id' => $ruleId,
            'qty' => 1,
            'format' => $defaultFormat,
            'length' => $this->couponHelper->getDefaultLength(),
            'prefix' => $this->couponHelper->getDefaultPrefix(),
            'suffix' => $this->couponHelper->getDefaultSuffix(),
            'dash' => $this->couponHelper->getDefaultDashInterval(),
        ];
    }
}
