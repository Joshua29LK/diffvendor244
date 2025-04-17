<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form\Email;

/**
 * Class StoreViewField
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class EmailSend extends \Magento\Ui\Component\Container
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if ($this->context->getDataProvider()->isPredictionEnabled()) {
            $hiddenFieldNames = ['email_send_hours', 'email_send_minutes'];
        } else {
            $hiddenFieldNames =  ['when'];
        }

        foreach ($this->components as $component) {
            if (in_array($component->getName(), $hiddenFieldNames)) {
                $config = $component->getConfig();
                $config['visible'] = false;
                $component->setConfig($config);
            }
        }
        parent::prepare();
    }
}
