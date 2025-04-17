<?php
namespace Aheadworks\Followupemail2\Model\ResourceModel\Order;

use Aheadworks\Followupemail2\Model\Serializer;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Quote
 * @package Aheadworks\Followupemail2\Model\ResourceModel\Order
 * @codeCoverageIgnore
 */
class Quote extends AbstractDb
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var Serializer
     */
    private $fueSerializer;

    /**
     * @param Context $context
     * @param ItemFactory $itemFactory
     * @param Serializer $fueSerializer
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        ItemFactory $itemFactory,
        Serializer $fueSerializer,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->itemFactory = $itemFactory;
        $this->fueSerializer = $fueSerializer;
    }
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_fue2_order_quote', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setQuoteData($this->fueSerializer->serialize($this->getPreparedQuoteData($object->getQuoteData())));

        return parent::save($object);
    }

    /**
     * {@inheritdoc}
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        parent::load($object, $value, $field);
        $quoteData = $object->getQuoteData() ? $this->fueSerializer->unserialize($object->getQuoteData()) : [];
        $object->setQuoteData($this->restoreQuoteData($quoteData));

        return $this;
    }

    /**
     * Get prepared quote data
     *
     * @param array $data
     * @return string
     */
    private function getPreparedQuoteData(array $data)
    {
        foreach ($data as $key => $value) {
            if ((is_array($value) || is_object($value))) {
                if ($key == 'items') {
                    foreach ($value as $itemIndex => $itemValue) {
                        $data[$key][$itemIndex] = $this->getPreparedQuoteData($itemValue->getData());
                    }
                } else {
                    unset($data[$key]);
                }
            }

            if (isset($data[$key]) && !is_array($data[$key]) && preg_match("/\r\n|\r|\n/", $value ?? '')) {
                $data[$key] = preg_replace("/\r\n|\r|\n/", "", $value);
            }
        }
        return $data;
    }

    /**
     * Restore quote data
     *
     * @param array $data
     * @return array
     */
    private function restoreQuoteData(array $data)
    {
        if (isset($data['items'])) {
            $items = $data['items'];
            foreach ($items as $key => $item) {
                /** @var Item $itemObject */
                $itemObject = $this->itemFactory->create();
                $itemObject->setData($item);
                $data['items'][$key] = $itemObject;
            }
        }

        return $data;
    }
}
