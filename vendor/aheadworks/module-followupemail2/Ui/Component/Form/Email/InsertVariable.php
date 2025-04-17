<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form\Email;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;
use Aheadworks\Followupemail2\Model\Email\Variables\Resolver as VariablesResolver;

/**
 * Class InsertVariable
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class InsertVariable extends Field
{
    /**
     * @var VariablesResolver
     */
    private $variablesResolver;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param VariablesResolver $variablesResolver
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        VariablesResolver $variablesResolver,
        $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->variablesResolver = $variablesResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $variablesJsSource = $this->variablesResolver->getVariableJsSource();

        if ($variablesJsSource) {
            $config['variablesJsSource'] = $variablesJsSource;
            $this->setData('config', $config);
        }

        parent::prepare();
    }
}
