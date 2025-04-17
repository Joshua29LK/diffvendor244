<?php

namespace Amasty\Affiliate\Model\ResourceModel;

use \Magento\Framework\Model\AbstractModel;

class Banner extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * Banner constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\EntityManager\EntityManager $entityManager
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_affiliate_banner', 'banner_id');
    }

    /**
     * {@inheritdoc}
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $loadedObject = $this->entityManager->load($object, $value);

        return $loadedObject;
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
