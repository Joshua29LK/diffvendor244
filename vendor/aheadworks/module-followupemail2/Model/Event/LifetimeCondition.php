<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Sale\Collection as SaleCollection;
use Magento\Sales\Model\ResourceModel\Sale\CollectionFactory as SaleCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Aheadworks\Followupemail2\Model\Serializer;

/**
 * Class LifetimeConditions
 * @package Aheadworks\Followupemail2\Model\Event
 */
class LifetimeCondition
{
    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * @var array
     */
    private $defaultParamsMap;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string[]
     */
    private $params;

    /**
     * @var SaleCollectionFactory
     */
    private $saleCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param SaleCollectionFactory $saleCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param StoreRepositoryInterface $storeRepository
     * @param Serializer $serializer
     */
    public function __construct(
        SaleCollectionFactory $saleCollectionFactory,
        StoreManagerInterface $storeManager,
        StoreRepositoryInterface $storeRepository,
        Serializer $serializer
    ) {
        $this->saleCollectionFactory = $saleCollectionFactory;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->serializer = $serializer;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        if (!$this->defaultOptions) {
            $this->defaultOptions = [
                'lt'    => __('less than'),
                'gt'    => __('more than'),
                'lteq'  => __('equals or less than'),
                'gteq'  => __('equals or more than'),
                'range' => __('range'),
            ];
        }
        return $this->defaultOptions;
    }

    /**
     * Get map of all params
     *
     * @return array
     */
    public function getDefaultParamsMap()
    {
        if (!$this->defaultParamsMap) {
            $this->defaultParamsMap = [
                'lt'    => ['value'],
                'gt'    => ['value'],
                'lteq'  => ['value'],
                'gteq'  => ['value'],
                'range' => ['from', 'to'],
            ];
        }
        return $this->defaultParamsMap;
    }

    /**
     * Perform validate of the customer specified (id or email)
     *
     * @param int|string $customer
     * @param int $storeId
     * @return bool
     */
    public function validate($customer, $storeId = 0)
    {
        if (is_numeric($customer)) {
            return $this->validateByCustomerId($customer);
        }

        return $this->validateByEmail($customer, $storeId);
    }

    /**
     * Get conditions serialized
     *
     * @return string
     */
    public function getConditionsSerialized()
    {
        $data = [];
        if ($this->operator && $this->params) {
            $data['operator'] = $this->operator;
            $data['params'] = $this->params;
        }
        return $this->serializer->serialize($data);
    }

    /**
     * Set conditions from serialized data
     *
     * @param string $serializedData
     * @return $this
     * @throws \Exception
     */
    public function setConditionsSerialized($serializedData)
    {
        $data = $this->serializer->unserialize($serializedData);
        if (!isset($data['operator']) || !isset($data['params'])) {
            throw new \Exception('Serialized data is not valid!');
        }
        $paramsMap = $this->getDefaultParamsMap();
        $paramsValues = $paramsMap[$data['operator']];
        $params = [];
        foreach ($paramsValues as $param) {
            $params[$param] = $data['params'][$param];
        }
        $this->setOperator($data['operator']);
        $this->setParams($params);
        return $this;
    }

    /**
     * Get operator
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set operator
     *
     * @param string $operator
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set params
     *
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Perform validation of customer sales
     *
     * @param int $customerId
     * @return bool
     */
    private function validateByCustomerId($customerId)
    {
        /** @var SaleCollection $collection */
        $collection = $this->saleCollectionFactory->create();
        $collection
            ->setCustomerIdFilter($customerId)
            ->setOrderStateFilter([
                Order::STATE_PROCESSING,
                Order::STATE_COMPLETE
            ])
            ->load();
        $customerTotals = $collection->getTotals();

        return $this->validateSales($customerTotals->getBaseLifetime());
    }

    /**
     * Perform validation of guest sales
     *
     * @param string $email
     * @param int $storeId
     * @return bool
     */
    private function validateByEmail($email, $storeId)
    {
        /** @var StoreInterface $store */
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();
        $storeIds = $this->getAllStoreIdsForWebsite($websiteId);

        /** @var SaleCollection $collection */
        $collection = $this->saleCollectionFactory->create();
        $collection
            ->addFieldToFilter('customer_email', $email)
            ->addFieldToFilter('store_id', $storeIds)
            ->setOrderStateFilter([
                Order::STATE_PROCESSING,
                Order::STATE_COMPLETE
            ])
            ->load();
        $customerTotals = $collection->getTotals();

        return $this->validateSales($customerTotals->getBaseLifetime());
    }

    /**
     * Get all website store ids
     *
     * @param int $websiteId
     * @return array
     */
    private function getAllStoreIdsForWebsite($websiteId)
    {
        /** @var StoreInterface[] $stores */
        $stores = $this->storeRepository->getList();

        $storeIds = [];
        foreach ($stores as $store) {
            if ($store->getWebsiteId() == $websiteId) {
                $storeIds[] = $store->getId();
            }
        }
        return $storeIds;
    }

    /**
     * Perform validation of total sales
     *
     * @param float $total
     * @return bool
     */
    private function validateSales($total)
    {
        $result = null;
        if ($this->operator) {
            if ($this->params && count($this->params) > 0) {
                switch ($this->operator) {
                    case 'lt':
                        if ($this->params['value']) {
                            $result = $total < $this->params['value'];
                        }
                        break;
                    case 'gt':
                        if ($this->params['value']) {
                            $result = $total > $this->params['value'];
                        }
                        break;
                    case 'lteq':
                        if ($this->params['value']) {
                            $result = $total <= $this->params['value'];
                        }
                        break;
                    case 'gteq':
                        if ($this->params['value']) {
                            $result = $total >= $this->params['value'];
                        }
                        break;
                    case 'range':
                        $from = true;
                        if ($this->params['from']) {
                            $from = $total >= $this->params['from'];
                        }
                        $to = true;
                        if ($this->params['to']) {
                            $to = $total <= $this->params['to'];
                        }
                        $result = ($from && $to);
                        break;
                    default:
                        $result = false;
                }
            }
            if ($result === null) {
                $result = true;
            }
        }
        return $result ?: false;
    }
}
