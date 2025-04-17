<?php

namespace Amasty\Affiliate\Model;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var ResourceModel\Coupon\CollectionFactory
     */
    protected $rowCollection;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var CouponListProvider
     */
    private $couponListProvider;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $collection,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Affiliate\Model\CouponListProvider $couponListProvider,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $collection;
        $this->rowCollection = $collectionFactory;
        $this->request = $request;
        $this->couponListProvider = $couponListProvider;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $id = $this->request->getParam('affiliateCouponId');
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $collection = $this->rowCollection->create()->addFieldToFilter('entity_id', $id);
        $items = $collection->getItems();
        $this->loadedData[$id] = [];
        foreach ($items as $item) {
            $customCouponCollection = $this->couponListProvider
                ->getCustomCouponCollection($item['account_id'], $item['program_id']);
            foreach ($customCouponCollection as $customCoupons) {
                $this->loadedData[$item->getId()]['dynamic_rows_container'][] = $customCoupons->getData();
            }
        }

        return $this->loadedData;
    }
}
