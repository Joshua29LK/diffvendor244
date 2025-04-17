<?php

namespace Amasty\Affiliate\Model\ResourceModel;

use \Magento\Framework\Model\AbstractModel;

class Account extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_affiliate_account';

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\SalesRule\Api\CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var Coupon
     */
    private $couponResource;

    /**
     * @var \Amasty\Affiliate\Model\Coupon
     */
    private $coupon;

    /**
     * Account constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\SalesRule\Api\CouponRepositoryInterface $couponRepository
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\SalesRule\Api\CouponRepositoryInterface $couponRepository,
        \Amasty\Affiliate\Model\ResourceModel\Coupon $couponResource,
        \Amasty\Affiliate\Model\Coupon $coupon,
        $connectionName = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->couponRepository = $couponRepository;
        $this->couponResource = $couponResource;
        $this->coupon = $coupon;
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('amasty_affiliate_account', 'account_id');
    }

    /**
     * {@inheritdoc}
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        /** @var \Amasty\Affiliate\Model\Account $loadedObject */
        $loadedObject = $this->entityManager->load($object, $value);
        if ($loadedObject->getAccountId() != null) {
            /** @var \Magento\Customer\Model\Data\Customer $customer */
            $customer = $this->customerRepository->getById($loadedObject->getCustomerId());

            $data['email'] = $customer->getEmail();
            $data['firstname'] = $customer->getFirstname();
            $data['lastname'] = $customer->getLastname();
            $loadedObject->addData($data);
        }

        return $loadedObject;
    }

    /**
     * @param \Amasty\Affiliate\Model\Account $account
     * @param $value
     * @param $parameter
     * @return \Amasty\Affiliate\Model\Account
     */
    public function loadBy(\Amasty\Affiliate\Model\Account $account, $value, $parameter)
    {
        $connection = $this->getConnection();
        $bind = ["$parameter" => $value];

        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            "$parameter = :$parameter"
        );

        if ($parameter == 'code') {
            $select
                ->joinLeft(
                    ['amasty_affiliate_coupon' => $this->getTable('amasty_affiliate_coupon')],
                    $this->getMainTable() . '.account_id = amasty_affiliate_coupon.account_id'
                )->joinLeft(
                    ['salesrule_coupon' => $this->getTable('salesrule_coupon')],
                    'amasty_affiliate_coupon.coupon_id = salesrule_coupon.coupon_id'
                );
        }

        $accountId = $connection->fetchOne($select, $bind);
        if ($accountId) {
            $this->load($account, $accountId);
        } else {
            $account->setData([]);
        }

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
