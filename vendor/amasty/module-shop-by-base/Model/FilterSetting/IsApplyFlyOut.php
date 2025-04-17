<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model\FilterSetting;

use Amasty\Shopby\Model\Source\SubcategoriesView;
use Amasty\ShopbyBase\Model\Detection\MobileDetect;

class IsApplyFlyOut
{
    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    public function __construct(
        MobileDetect $mobileDetect
    ) {
        $this->mobileDetect = $mobileDetect;
    }

    public function execute(int $subcategoriesView): bool
    {
        return ($subcategoriesView == SubcategoriesView::FLY_OUT)
            || ($subcategoriesView == SubcategoriesView::FLY_OUT_FOR_DESKTOP_ONLY && !$this->mobileDetect->isMobile());
    }
}
