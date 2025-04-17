<?php
namespace Aheadworks\Followupemail2\Ui\Component\Form\Email;

/**
 * Class StoreViewField
 * @package Aheadworks\Followupemail2\Ui\Component\Form
 * @codeCoverageIgnore
 */
class EmailSendMinutes extends \Magento\Ui\Component\Form\Field
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if ($this->context->getDataProvider()->isCurrentEmailFirst()) {
            $config = $this->getConfig();
            $config['additionalInfo'] = __('after event triggered');
            $this->setConfig($config);
        }
        parent::prepare();
    }
}
