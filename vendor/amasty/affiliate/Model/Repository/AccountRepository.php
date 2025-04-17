<?php

namespace Amasty\Affiliate\Model\Repository;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\Data;
use Amasty\Affiliate\Api\Data\AccountInterfaceFactory;
use Amasty\Affiliate\Model\NotificationSender;
use Amasty\Affiliate\Model\ResourceModel\Account;
use Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory;
use Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class AccountRepository extends AbstractRepository implements AccountRepositoryInterface
{
    public const REFFERING_CODE = 'referring_code';
    public const CODE = 'code';
    public const CUSTOMER_ID = 'customer_id';

    /**
     * @var Account
     */
    private $resource;

    /**
     * @var AccountInterfaceFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $accountById = [];

    /**
     * @var array
     */
    private $accountByCustomerId = [];

    /**
     * @var array
     */
    private $accountByCouponCode = [];

    /**
     * @var array
     */
    private $accountByRefferingCode = [];

    /**
     * @var AttributeSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CouponCollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var int
     */
    private $currentCustomerId;

    /**
     * @var NotificationSender
     */
    private $notificationSender;

    public function __construct(
        Account $resource,
        AccountInterfaceFactory $factory,
        AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CouponCollectionFactory $couponCollectionFactory,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager,
        NotificationSender $notificationSender
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->notificationSender = $notificationSender;
    }

    /**
     * @param Data\AccountInterface $entity
     * @return Data\AccountInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\AccountInterface $entity)
    {
        if ($entity->getAccountId()) {
            $newStatus = $entity->getIsAffiliateActive();
            $oldStatus = $this->get($entity->getAccountId())->getIsAffiliateActive();
            if ($oldStatus != $newStatus) {
                $this->notificationSender->sendAffiliateStatusEmail($entity, $newStatus);
            }
        }

        try {
            $this->resource->save($entity);
            unset($this->accountById[$entity->getAccountId()]);
        } catch (\Exception $e) {
            if ($entity->getAccountId()) {
                throw new CouldNotSaveException(
                    __('Unable to save account with ID %1. Error: %2', [$entity->getAccountId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new account. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * @param int $id
     * @return Data\AccountInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        if (!isset($this->accountById[$id])) {
            /** @var \Amasty\Affiliate\Model\Account $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getAccountId()) {
                throw new NoSuchEntityException(__('Account with specified ID "%1" not found.', $id));
            }
            $this->accountById[$id] = $entity;
        }
        return $this->accountById[$id];
    }

    /**
     * @param Data\AccountInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\AccountInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->accountById[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getAccountId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove account with ID %1. Error: %2', [$entity->getAccountId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove account. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param int|null $customerId
     * @return bool
     */
    public function isAffiliate($customerId = null)
    {
        $isAffiliate = false;

        if ($customerId == null) {
            $customerId = $this->getCurrentCustomerId();
        }

        /** @var Account $customer */
        $affiliate = $this->getByCustomerId($customerId);

        if ($affiliate->getAccountId()) {
            $isAffiliate = true;
        }

        return $isAffiliate;
    }

    /**
     * @param int $customerId
     * @return Data\AccountInterface
     */
    public function getByCustomerId($customerId)
    {
        if (isset($this->accountByCustomerId[$customerId])) {
            return $this->accountByCustomerId[$customerId];
        }

        /** @var Account $customer */
        $account = $this->factory->create();
        $account = $this->loadBy($account, $customerId, self::CUSTOMER_ID);
        if ($account->getCustomerId()) {
            $this->accountByCustomerId[$customerId] = $account;
            $this->accountById[$account->getAccountId()] = $account;
            $this->accountByRefferingCode[$account->getReferringCode()] = $account;
        }

        return $account;
    }

    /**
     * @param string $couponCode
     * @return Data\AccountInterface
     * @throws NoSuchEntityException
     */
    public function getByCouponCode($couponCode)
    {
        if (isset($this->accountByCouponCode[$couponCode])) {
            return $this->accountByCouponCode[$couponCode];
        }

        /** @var Account $customer */
        $account = $this->factory->create();
        $account = $this->loadBy($account, $couponCode, self::CODE);

        if (!$account->getAccountId()) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    [
                        'fieldName' => 'code',
                        'fieldValue' => $couponCode
                    ]
                )
            );
        } else {
            $this->accountByCustomerId[$account->getCustomerId()] = $account;
            $this->accountByCouponCode[$couponCode] = $account;
            $this->accountById[$account->getAccountId()] = $account;
            $this->accountByRefferingCode[$account->getReferringCode()] = $account;
        }

        return $account;
    }

    /**
     * @param string $code
     * @return Data\AccountInterface
     * @throws NoSuchEntityException
     */
    public function getByReferringCode($code)
    {
        if (isset($this->accountByRefferingCode[$code])) {
            return $this->accountByRefferingCode[$code];
        }

        /** @var Account $customer */
        $account = $this->factory->create();
        $account = $this->loadBy($account, $code, self::REFFERING_CODE);

        if (!$account->getAccountId()) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    [
                        'fieldName' => 'referring_code',
                        'fieldValue' => $code
                    ]
                )
            );
        } else {
            $this->accountByCustomerId[$account->getCustomerId()] = $account;
            $this->accountById[$account->getAccountId()] = $account;
            $this->accountByRefferingCode[$code] = $account;
        }

        return $account;
    }

    /**
     * @param Data\AccountInterface $entity
     * @param int $param
     * @param string $type
     * @return Data\AccountInterface
     */
    private function loadBy($entity, $param, $type)
    {
        $entity = $this->resource->loadBy($entity, $param, $type);

        return $entity;
    }

    /**
     * Save Current Customer Id for Ajax Request
     *
     * @return int|null
     */
    protected function getCurrentCustomerId()
    {
        if (!$this->currentCustomerId) {
            $this->currentCustomerId = $this->customerSession->getCustomerId();
        }

        return $this->currentCustomerId;
    }
}
