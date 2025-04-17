<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GeneralConditions
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class GeneralConditions extends \Magento\Ui\Component\Form\Fieldset
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if ($this->isNeedToHideFieldset()) {
            $this->hideFieldset();
        }
        parent::prepare();
    }

    /**
     * Check if need to hide fieldset block
     *
     * @return bool
     */
    private function isNeedToHideFieldset()
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * Hide fieldset block
     */
    private function hideFieldset()
    {
        $config = $this->getConfig();
        $config['visible'] = false;
        $this->setConfig($config);
    }
}
