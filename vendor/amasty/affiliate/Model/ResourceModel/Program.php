<?php

namespace Amasty\Affiliate\Model\ResourceModel;

use Amasty\Affiliate\Api\Data\ProgramInterface;
use Magento\Framework\Model\AbstractModel;

class Program extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    public const TABLE_NAME = 'amasty_affiliate_program';

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * @var Account\CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var \Amasty\Affiliate\Model\CouponFactory
     */
    private $couponFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory $accountCollectionFactory,
        \Amasty\Affiliate\Model\CouponFactory $affiliateCouponFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->couponFactory = $affiliateCouponFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ProgramInterface::PROGRAM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        return $this->entityManager->load($object, $value);
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
