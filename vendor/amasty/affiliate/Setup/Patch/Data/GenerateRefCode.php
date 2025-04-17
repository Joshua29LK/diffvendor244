<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Setup\Patch\Data;

use Amasty\Affiliate\Model\Account;
use Amasty\Affiliate\Model\CouponCreator;
use Amasty\Affiliate\Model\RefferingCodesManagement;
use Amasty\Affiliate\Model\ResourceModel\Account\Collection;
use Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class GenerateRefCode implements DataPatchInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CouponCreator
     */
    private $couponCreator;

    /**
     * @var RefferingCodesManagement
     */
    private $refferingCodesManagement;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        CollectionFactory $collectionFactory,
        CouponCreator $couponCreator,
        RefferingCodesManagement $refferingCodesManagement,
        State $appState
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->couponCreator = $couponCreator;
        $this->refferingCodesManagement = $refferingCodesManagement;
        $this->appState = $appState;
    }

    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'generateReferringCode']);
    }
    
    public function generateReferringCode(): void
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $accounts = $collection->getItems();

        /** @var Account $account */
        foreach ($accounts as $account) {
            if (!$account->getReferringCode()) {
                $account->setReferringCode($this->refferingCodesManagement->generateReferringCode());
                $this->couponCreator->addCoupon($account->getAccountId());
            }
        }

        $collection->save();
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
