<?php

namespace Amasty\Affiliate\Block\Adminhtml\Withdrawal\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class PayButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        /** @var \Amasty\Affiliate\Model\Withdrawal $currentWithdrawal */
        $currentWithdrawal = $this->getCurrentWithdrawal();
        $data = [];
        if ($currentWithdrawal->getStatus() == $currentWithdrawal::STATUS_PENDING) {
            $data = [
                'label' => __('Pay'),
                'class' => 'save primary',
                'on_click' => sprintf("location.href = '%s';", $this->getPayUrl($currentWithdrawal->getTransactionId()))
            ];
        }

        return $data;
    }

    public function getPayUrl($withdrawalId)
    {
        return $this->getUrl('amasty_affiliate/withdrawal/pay', ['id' => $withdrawalId]);
    }
}
