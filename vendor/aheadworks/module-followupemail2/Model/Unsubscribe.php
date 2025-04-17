<?php
namespace Aheadworks\Followupemail2\Model;

use Aheadworks\Followupemail2\Api\Data\UnsubscribeInterface;
use Aheadworks\Followupemail2\Api\Data\UnsubscribeExtensionInterface;
use Aheadworks\Followupemail2\Model\ResourceModel\Unsubscribe as UnsubscribeResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Unsubscribe
 * @package Aheadworks\Followupemail2\Model
 * @codeCoverageIgnore
 */
class Unsubscribe extends AbstractModel implements UnsubscribeInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(UnsubscribeResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($Id)
    {
        return $this->setData(self::ID, $Id);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(UnsubscribeExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
