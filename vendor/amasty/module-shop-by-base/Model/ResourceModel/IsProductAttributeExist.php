<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model\ResourceModel;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class IsProductAttributeExist extends AbstractDb
{
    public const ATTRIBUTE_TABLE = 'eav_attribute';

    protected function _construct()
    {
        $this->_init(self::ATTRIBUTE_TABLE, AttributeInterface::ATTRIBUTE_CODE);
    }

    /**
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $attributeCode): bool
    {
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()]
        )->where(
            sprintf('%s = ?', AttributeInterface::ATTRIBUTE_CODE),
            $attributeCode
        )->where(
            sprintf('%s = ?', AttributeInterface::ENTITY_TYPE_ID),
            CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID
        );

        return (bool) $this->getConnection()->fetchOne($select);
    }
}
