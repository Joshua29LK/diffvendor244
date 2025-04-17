<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CABAV
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Model\ResourceModel;

use Bss\CABAV\Model\Indexer\AlsoBought;

/**
 * Class ConnectionDB
 * @package Bss\CABAV\Model\ResourceModel
 */
class ConnectionDB
{
    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var array
     */
    protected $item = [];

    /**
     * @var array
     */
    protected $oldOrderIds = [];

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * ConnectionDB constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
        $this->writeAdapter = $this->resourceConnection->getConnection('core_write');
    }

    /**
     * @param array $data
     * @param strig $type
     * @return $this
     */
    public function reindexData($data, $type)
    {
        if (!$this->reindexDataType($data, $type)) {
            return;
        }
        $updateAll = false;
        if ($type == AlsoBought::BSS_CABAV_INDEXER_UPDATE_ALL) {
            $updateAll = true;
        }

        $items = $this->item;
        foreach ($items as $productId => $value) {
            $storeId = $value['store_id'];
            $qtyOrdered = (int)$value['qty_ordered'];
            $newAlsoBoughtProduct = $value['also_bought_product'];
            $data = [
                'product_id' => $productId,
                'qty_ordered' => $qtyOrdered,
                'store_id' => $storeId,
                'also_bought_product' => implode(",", $newAlsoBoughtProduct)
            ];
            if ($updateAll) {
                $this->insertData($data);
            } else {
                $this->deleteOldAlsoBoughtItem($productId, $value['store_id']);
                $this->insertData($data);
            }
        }
        return $this;
    }

    /**
     * @param array $data
     * @param string $type
     * @return bool
     */
    protected function reindexDataType($data, $type)
    {
        if ($type == AlsoBought::BSS_CABAV_INDEXER_UPDATE_ALL) {
            $this->writeAdapter->truncateTable($this->getTableName('bss_also_product_qty_ordered'));
            $orderList = $this->getOrderList(false);
            foreach ($orderList as $orderId) {
                $this->getItems($orderId);
            }
        } elseif ($type == AlsoBought::BSS_CABAV_INDEXER_UPDATE_ON_SAVE) {
            $data = $this->serializer->unserialize($data);
            foreach ($data['list_items'] as $productId => $item) {
                $this->getOldOrderIds($productId, $item['store_id']);
            }
            foreach ($this->oldOrderIds as $orderId) {
                $this->getItems($orderId);
            }
            $this->item = $this->processData($data['list_items'], $data['order_productids']);
        } elseif ($type == AlsoBought::BSS_CABAV_INDEXER_UPDATE_SCHEDULE) {
            $ids = array_unique($data);
            foreach ($ids as $orderId) {
                $this->getItems($orderId);
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * @param string $incrementId
     * @return string
     */
    protected function getOrderIdByIncrementId($incrementId)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('sales_order')],
                ['entity_id']
            )->where('increment_id = :increment_id');
        $bind = [
            ':increment_id' => $incrementId,
        ];
        $entityId = $this->readAdapter->fetchOne($select, $bind);
        return $entityId;
    }

    /**
     * @param string $fromDate
     * @return array
     */
    public function getOrderList($fromDate)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('sales_order')],
                ['entity_id']
            );
        if ($fromDate) {
            $select->where("created_at > '" . $fromDate . "'");
        }
        return $this->readAdapter->fetchCol($select);
    }

    /**
     * @param string $orderId
     * @return mixed
     */
    protected function getItems($orderId)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('sales_order_item')]
            )
            ->where('order_id = :order_id');
        $bind = [
            ':order_id' => $orderId,
        ];
        //@codingStandardsIgnoreStart
        $result = $this->readAdapter->fetchAll($select, $bind);
        //@codingStandardsIgnoreEnd
        $orderProductIds = [];
        $listItems = [];
        foreach ($result as $row) {
            $productType = $row['product_type'];
            $parentItem = $row['parent_item_id'];
            $qtyOrdered = (int)$row['qty_ordered'];
            $productId = $row['product_id'];
            $storeId = $row['store_id'];
            if ($productType == 'grouped') {
                $productOptions = $row['product_options'];
                $productOptions = $this->serializer->unserialize($productOptions);
                if (isset($productOptions['super_product_config']) &&
                    $productOptions['super_product_config']['product_id']
                ) {
                    $productId = $productOptions['super_product_config']['product_id'];
                }
            } elseif ($productType == 'simple' && $parentItem) {
                continue;
            }
            if (isset($listItems[$productId])) {
                $listItems[$productId]['qty_ordered'] = (int)$qtyOrdered + $listItems[$productId]['qty_ordered'];
            } else {
                $listItems[$productId]['store_id'] = $storeId;
                $listItems[$productId]['qty_ordered'] = $qtyOrdered;
            }
            $orderProductIds[$productId] = $productId;
        }
        $this->item = $this->processData($listItems, $orderProductIds);
        return true;
    }

    /**
     * @param array $listItems
     * @param array $orderProductIds
     * @return array
     */
    protected function processData($listItems, $orderProductIds)
    {
        $itemsFinal = $this->item;
        foreach ($listItems as $productId => $data) {
            $qtyOrdered = $data['qty_ordered'];
            $productList = $orderProductIds;
            unset($productList[$productId]);
            $storeId = $data['store_id'];

            if (isset($itemsFinal[$productId])) {
                $currentQtyOrdered = $itemsFinal[$productId]['qty_ordered'];
                $currentProductList = $itemsFinal[$productId]['also_bought_product'];
                $productList = array_unique(array_merge($productList, $currentProductList));
                $qtyOrdered = $qtyOrdered + $currentQtyOrdered;
            }
            $itemsFinal[$productId]['store_id'] = $storeId;
            $itemsFinal[$productId]['qty_ordered'] = $qtyOrdered;
            $itemsFinal[$productId]['also_bought_product'] = $productList;
        }
        return $itemsFinal;
    }

    /**
     * @param string $productId
     * @param string $storeId
     * @return array
     */
    protected function getOldOrderIds($productId, $storeId)
    {
        $oldOrderIdsList = $this->oldOrderIds;
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('sales_order_item')],
                ['order_id']
            )
            ->where('product_id = :product_id');
        //@codingStandardsIgnoreStart
        $select->orWhere('product_options like :product_id');
        //@codingStandardsIgnoreEnd
        $select->where('store_id = :store_id');
        $bind = [
            ':product_id' => '%"product_id":"' . $productId . '"' . '%',
            ':store_id' => $storeId
        ];
        $result = $this->readAdapter->fetchCol($select, $bind);
        $this->oldOrderIds = array_unique(array_merge($oldOrderIdsList, $result));
        return $this->oldOrderIds;
    }

    /**
     * @param string $productId
     * @param string $storeId
     * @return string
     */
    protected function deleteOldAlsoBoughtItem($productId, $storeId)
    {
        $condition = 'product_id =' . $productId . ' AND store_id =' . $storeId;
        $this->writeAdapter->delete($this->getTableName('bss_also_product_qty_ordered'), $condition);
        return true;
    }

    /**
     * @param array $data
     */
    protected function insertData($data)
    {
        try {
            $this->writeAdapter->insert($this->getTableName('bss_also_product_qty_ordered'), $data);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @param string $entity
     * @return bool|mixed
     */
    public function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }

    /**
     * @param string $productId
     * @param $filterCondition
     * @return array
     */
    public function getProductAlsoView($productId, $filterCondition)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('alsoviewed_log')],
                ['customer_session']
            )
            ->where('product_id = ?', $productId);
        if (!empty($filterCondition)) {
            $select->where('customer_session not in (?)', $filterCondition);
        }
        $selectList = $this->readAdapter->select()
            ->from(
                [$this->getTableName('alsoviewed_log')],
                ['product_id']
            )
            ->where('customer_session in (?)', $select)
            ->where('product_id != ?', $productId);
        //@codingStandardsIgnoreStart
        $selectList->group('product_id');
        //@codingStandardsIgnoreEnd
        return $this->readAdapter->fetchCol($selectList);
    }

    /**
     * @param string $productId
     * @param $customerSession
     */
    public function insertProductAlsoView($productId, $customerSession)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('alsoviewed_log')],
                ['entity_id']
            )
            ->where('product_id = ?', $productId)
            ->where('customer_session = ?', $customerSession);
        $result = $this->readAdapter->fetchCol($select);
        if (empty($result)) {
            $data = [
                'product_id' => $productId,
                'customer_session' => $customerSession
            ];
            $this->writeAdapter->insert($this->getTableName('alsoviewed_log'), $data);
        }
    }

    /**
     * @param string $productId
     * @return string
     */
    public function getProductAlsoBought($productId)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('bss_also_product_qty_ordered')],
                ['also_bought_product']
            )
            ->where('product_id = :product_id');
        $bind = [
            ':product_id' => $productId,
        ];
        return $this->readAdapter->fetchOne($select, $bind);
    }

    /**
     * @param string $productIds
     * @param $customerSession
     * @return \Magento\Framework\DB\Select
     */
    public function jointCountColumn($productIds, $customerSession)
    {
        $select = $this->readAdapter->select()
            ->from(
                [$this->getTableName('alsoviewed_log')],
                ['product_id', 'count' => 'count(*)']
            )
            ->where('product_id in (?)', $productIds)
            ->where('customer_session not in (?)', $customerSession);
        //@codingStandardsIgnoreStart
        $select->group('product_id');
        //@codingStandardsIgnoreEnd
        return $select;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function alsoBoughtJoinLeft($collection)
    {
        $collection->getSelect()->joinLeft(
            ['also_bought_table' => $this->getTableName('bss_also_product_qty_ordered')],
            'e.entity_id= also_bought_table.product_id',
            []
        );
        $collection->getSelect()->order('also_bought_table.qty_ordered DESC');
        return $collection;
    }

    /**
     * @param $collection
     * @param $jointTable
     * @return mixed
     */
    public function alsoViewJoinLeft($collection, $jointTable)
    {
        $collection->getSelect()->joinLeft(
            ['also_view_table' => $jointTable],
            'e.entity_id= also_view_table.product_id',
            ['count']
        );
        $collection->getSelect()->order('also_view_table.count DESC');
        return $collection;
    }

    /**
     * @param $collection
     * @param $limit
     * @return mixed
     */
    public function limitCollection($collection, $limit)
    {
        $collection->getSelect()->limit($limit);
        return $collection;
    }
}