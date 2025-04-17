<?php

namespace Amasty\Affiliate\Block\Account;

class Traffic extends \Amasty\Affiliate\Block\Account\Social
{
    /**
     * @var string
     */
    protected $_template = 'account/traffic.phtml';

    public function getText()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/friends/text_traffic');
    }
}
