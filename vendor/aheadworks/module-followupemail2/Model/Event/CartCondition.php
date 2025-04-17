<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Magento\Rule\Model\Condition\Combine as ConditionCombine;
use Magento\Rule\Model\Action\Collection as ActionCollection;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory as ConditionCombineFactory;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory as ConditionProductCombineFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Aheadworks\Followupemail2\Model\Serializer;

/**
 * Class CartCondition
 * @package Aheadworks\Followupemail2\Model\Event
 * @codeCoverageIgnore
 */
class CartCondition extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Cart condition id
     */
    const CONDITION_ID = 1;

    /**
     * @var ConditionCombineFactory
     */
    private $condCombineFactory;

    /**
     * @var ConditionProductCombineFactory
     */
    private $condProdCombineFactory;

    /**
     * @var Serializer
     */
    private $fueSerializer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param ConditionCombineFactory $condCombineFactory
     * @param ConditionProductCombineFactory $condProdCombineFactory
     * @param Serializer $fueSerializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        ConditionCombineFactory $condCombineFactory,
        ConditionProductCombineFactory $condProdCombineFactory,
        Serializer $fueSerializer,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineFactory = $condProdCombineFactory;
        $this->fueSerializer = $fueSerializer;
        parent::__construct($context, $registry, $formFactory, $localeDate, null, null, $data);
    }

    /**
     * Getter for rule combine conditions instance
     *
     * @return ConditionCombine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return ActionCollection
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->fueSerializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    /**
     * Reset rule combine conditions
     *
     * @param null|ConditionCombine $conditions
     * @return $this
     */
    protected function _resetConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions
            ->setRule($this)
            ->setId(self::CONDITION_ID)
            ->setPrefix('conditions');

        $this->setConditions($conditions);

        return $this;
    }
}
