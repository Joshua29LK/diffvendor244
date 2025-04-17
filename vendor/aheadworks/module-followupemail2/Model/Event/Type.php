<?php
namespace Aheadworks\Followupemail2\Model\Event;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Type
 * @package Aheadworks\Followupemail2\Model\Event
 * @codeCoverageIgnore
 */
class Type extends DataObject implements TypeInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($data);
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler()
    {
        $handlerClass = $this->getData(self::HANDLER);
        /** @var HandlerInterface $eventHandler */
        $eventHandler = $this->objectManager->create($handlerClass);
        return $eventHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerConditionsEnabled()
    {
        return $this->getData(self::CUSTOMER_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function isCartConditionsEnabled()
    {
        return $this->getData(self::CART_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function isOrderConditionsEnabled()
    {
        return $this->getData(self::ORDER_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function isProductConditionsEnabled()
    {
        return $this->getData(self::PRODUCT_CONDITIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function isProductRulesEnabled()
    {
        return $this->getData(self::PRODUCT_RULES);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailPredictionEnabled()
    {
        return $this->getData(self::EMAIL_PREDICTION);
    }

    /**
     * {@inheritdoc}
     */
    public function isElementEnabled($element)
    {
        $result = false;
        switch ($element) {
            case self::CUSTOMER_CONDITIONS:
                $result = $this->isCustomerConditionsEnabled();
                break;
            case self::CART_CONDITIONS:
                $result = $this->isCartConditionsEnabled();
                break;
            case self::ORDER_CONDITIONS:
                $result = $this->isOrderConditionsEnabled();
                break;
            case self::PRODUCT_CONDITIONS:
                $result = $this->isProductConditionsEnabled();
                break;
            case self::PRODUCT_RULES:
                $result = $this->isProductRulesEnabled();
                break;
            case self::EMAIL_PREDICTION:
                $result = $this->isEmailPredictionEnabled();
                break;
            default:
        }
        return $result;
    }
}
