<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Rule\Validator;

use Amasty\Affiliate\Model\ResourceModel\Rule\CustomerAffiliateCouponCollection;
use Amasty\Affiliate\Model\ResourceModel\Rule\CustomerAffiliateCouponCollectionFactory;
use Amasty\Affiliate\Model\Rule\Condition\Affiliate as AffiliateCondition;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Affiliate
{
    /**
     * @var CustomerAffiliateCouponCollectionFactory
     */
    private $couponCollectionFactory;

    public function __construct(
        CustomerAffiliateCouponCollectionFactory $couponCollectionFactory
    ) {
        $this->couponCollectionFactory = $couponCollectionFactory;
    }

    /**
     * Validate model based on affiliate condition data
     *
     * @param AffiliateCondition $condition
     * @param AbstractModel $model
     * @return bool
     * @throws InputException
     */
    public function validate(AffiliateCondition $condition, AbstractModel $model): bool
    {
        $collection = $this->prepareAffiliateCouponCollection($model);

        if ($condition->getValueParsed()) {
            return $this->isNeverUsedCoupons($collection, $condition)
                || $this->validateUsedCoupons($collection, $condition);
        }

        return false;
    }

    /**
     * Prepare applied affiliate coupons collection for customer
     *
     * @param AbstractModel $model
     * @return CustomerAffiliateCouponCollection
     */
    private function prepareAffiliateCouponCollection(AbstractModel $model): CustomerAffiliateCouponCollection
    {
        $couponCollection = $this->couponCollectionFactory->create();
        $this->addCustomerCondition($model, $couponCollection);

        return $couponCollection;
    }

    /**
     * Add guest\registered customer condition to coupon collection
     *
     * @param AbstractModel $model
     * @param CustomerAffiliateCouponCollection $couponCollection
     * @return void
     */
    private function addCustomerCondition(
        AbstractModel $model,
        CustomerAffiliateCouponCollection $couponCollection
    ): void {
        if ($model->getCustomerIsGuest()) {
            $couponCollection->addFieldToFilter('o.customer_email', ['eq' => $model->getEmail()]);
            $couponCollection->addFieldToFilter('o.quote_id', ['eq' => $model->getQuoteId()]);
        } else {
            $couponCollection->addFieldToFilter('o.customer_id', ['eq' => $model->getId()]);
        }
    }

    /**
     * Checking orders of the current customer using affiliate coupons for negative conditions:
     *  "is not" and "does not contain".
     *  The segment include customers who have never used affiliate coupons for negative conditions.
     *
     * @param CustomerAffiliateCouponCollection $couponOrderCollection
     * @param AffiliateCondition $condition
     * @return bool
     */
    private function isNeverUsedCoupons(
        CustomerAffiliateCouponCollection $couponOrderCollection,
        AffiliateCondition $condition
    ): bool {
        if (in_array($condition->getOperator(), ['!=', '!{}'])) {
            $collection = clone $couponOrderCollection;

            return !$this->validateByCollection(
                $collection->addFieldToFilter('ac.referring_code', ['notnull' => true])
            );
        }

        return false;
    }

    /**
     * Checking orders of the current customer using coupons by rule condition
     *
     * @param CustomerAffiliateCouponCollection $couponOrderCollection
     * @param AffiliateCondition $condition
     * @return bool
     * @throws InputException
     */
    private function validateUsedCoupons(
        CustomerAffiliateCouponCollection $couponOrderCollection,
        AffiliateCondition $condition
    ): bool {
        $usedCoupons = $this->validateByCollection(
            $this->applyFilterToCollection($couponOrderCollection, $condition)
        );

        return in_array($condition->getOperator(), ['!=', '!{}'])
            ? !$usedCoupons
            : $usedCoupons;
    }

    /**
     * Checking if at least one record exists in the collection
     *
     * @param AbstractCollection $collection
     * @return bool
     */
    private function validateByCollection(AbstractCollection $collection): bool
    {
        return (bool)$collection->setPageSize(1)
            ->clear()
            ->count();
    }

    /**
     * Applying a selection condition to a collection based on a rule condition and its values
     *
     * @param AbstractCollection $collection
     * @param AffiliateCondition $condition
     * @return AbstractCollection
     * @throws InputException
     */
    private function applyFilterToCollection(
        AbstractCollection $collection,
        AffiliateCondition $condition
    ): AbstractCollection {
        $filterValues = $condition->getValueParsed();
        $conditions = [];
        $connection = $collection->getConnection();
        foreach ($filterValues as $filterValue) {
            $conditions[] = $connection->prepareSqlCondition(
                $condition->getAttribute(),
                [$this->mapRuleOperatorToCondition($condition->getOperator()) => $filterValue]
            );
        }

        $collection->getSelect()->where(implode(
            $condition->getOperator() == '()' ? ' OR ' : ' AND ',
            $conditions
        ));

        return $collection;
    }

    /**
     * Maps rule operators to matching operators in a collection filter
     *
     * @param string $ruleOperator
     * @return string
     * @throws InputException
     */
    private function mapRuleOperatorToCondition(string $ruleOperator): string
    {
        $operatorsMap = [
            '==' => 'finset',
            '!=' => 'finset',
            '()' => 'finset',
            '{}' => 'like',
            '!{}' => 'like'
        ];

        if (!array_key_exists($ruleOperator, $operatorsMap)) {
            throw new InputException(
                __(
                    'Undefined rule operator "%1" passed in. Valid operators are: %2',
                    $ruleOperator,
                    implode(',', array_keys($operatorsMap))
                )
            );
        }

        return $operatorsMap[$ruleOperator];
    }
}
