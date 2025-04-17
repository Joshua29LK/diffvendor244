<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LifetimeSalesField
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class LifetimeSalesField extends \Magento\Ui\Component\Form\Field
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $currency = $this->storeManager->getStore()->getCurrentCurrency();
        $currencySymbol = $currency->getCurrencySymbol();
        $config = $this->getConfig();
        $config['addbefore'] = $currencySymbol;
        $this->setConfig($config);
        parent::prepare();
    }
}
