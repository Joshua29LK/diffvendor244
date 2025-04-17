<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Followupemail2\Model\Event\TypeInterface as EventTypeInterface;

/**
 * Class CartConditions
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class CartConditions extends \Magento\Ui\Component\Form\Fieldset
{
    /**
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if (!$this->context->getDataProvider()->isElementEnabled(EventTypeInterface::CART_CONDITIONS)) {
            $config = $this->getConfig();
            $config['visible'] = false;
            $this->setConfig($config);
        }
        parent::prepare();
    }
}
