<?php

namespace Amasty\ShopbyBrand\Plugin\Catalog\Model\Layer;

use Amasty\ShopbyBase\Helper\FilterSetting;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\State as MagentoStateModel;
use Amasty\ShopbyBrand\Helper\Content;
use Magento\Catalog\Model\Layer\Filter\Item;

class State
{
    /**
     * @var  Content
     */
    protected $contentHelper;

    public function __construct(Content $contentHelper)
    {
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param MagentoStateModel $subject
     * @param callable $proceed
     * @param Item $filter
     * @return MagentoStateModel
     */
    public function aroundAddFilter(MagentoStateModel $subject, callable $proceed, $filter)
    {
        if ($this->isCurrentBranding($filter->getFilter())) {
            return $subject;
        }
        return $proceed($filter);
    }

    private function isCurrentBranding(FilterInterface $filter): bool
    {
        $brand = $this->contentHelper->getCurrentBranding();

        return $brand && (FilterSetting::ATTR_PREFIX . $filter->getRequestVar() === $brand->getFilterCode());
    }
}
