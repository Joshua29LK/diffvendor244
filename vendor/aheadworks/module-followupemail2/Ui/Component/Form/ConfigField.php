<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form;

/**
 * Class ConfigField
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class ConfigField extends \Magento\Ui\Component\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');

        if ($configSettingsUrl = $this->getData('config/configSettingsUrl')) {
            $config['configSettingsUrl'] = $this->getContext()->getUrl($configSettingsUrl);
        }
        $this->setData('config', $config);
    }
}
