<?php


namespace Amasty\Affiliate\Ui\Component\Listing\Columns;

use Amasty\Affiliate\Model\CouponListProvider;
use Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class CustomCoupons extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const NAME = 'column.custom_coupons';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CouponListProvider
     */
    private $couponListProvider;

    public function __construct(
        CollectionFactory $collectionFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CouponListProvider $couponListProvider,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->couponListProvider = $couponListProvider;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $string = $this->couponListProvider
                    ->getCustomCouponListAsString($item['account_id'], $item['program_id']);
                $item['custom_coupons'] = $string;
            }
        }

        return $dataSource;
    }
}
