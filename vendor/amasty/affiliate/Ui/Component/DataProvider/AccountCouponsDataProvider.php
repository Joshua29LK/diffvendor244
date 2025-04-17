<?php

namespace Amasty\Affiliate\Ui\Component\DataProvider;

use Amasty\Affiliate\Model\Repository\AccountRepository;
use Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class AccountCouponsDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        CouponCollectionFactory $couponCollectionFactory,
        AccountRepository $accountRepository,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $couponCollectionFactory->create();
        $this->request = $request;
        $this->accountRepository = $accountRepository;
        $this->customerRepository = $customerRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $accountId = $this->request->getParam('account_id');
        if ($accountId) {
            $customerId = $this->accountRepository->get($accountId)->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $this->getCollection()
                ->addAccountIdFilter($accountId)
                ->addProgramActiveFilter()
                ->addFieldToFilter('is_system', 1);
            $this->getCollection()->addProgramCustomerAndGroupIdFilter($customer->getId(), $customer->getGroupId());
        }
        $items = $this->getCollection()->getData();

        return [
            'totalRecords' => $this->getCollection()->count(),
            'items' => array_values($items)
        ];
    }
}
