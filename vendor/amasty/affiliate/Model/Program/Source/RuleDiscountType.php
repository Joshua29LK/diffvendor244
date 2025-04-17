<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Program\Source;

use Magento\SalesRule\Model\Rule;

class RuleDiscountType extends AbstractOptions
{
    public function toArray(): array
    {
        return [
            Rule::BY_PERCENT_ACTION => __('Percent of product price discount'),
            Rule::BY_FIXED_ACTION => __('Fixed amount discount'),
            Rule::CART_FIXED_ACTION => __('Fixed amount discount for whole cart'),
            Rule::BUY_X_GET_Y_ACTION => __('Buy X get Y free (discount amount is Y)')
        ];
    }
}
