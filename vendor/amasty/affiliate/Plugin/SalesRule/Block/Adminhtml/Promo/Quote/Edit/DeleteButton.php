<?php

namespace Amasty\Affiliate\Plugin\SalesRule\Block\Adminhtml\Promo\Quote\Edit;


class DeleteButton
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Affiliate\Model\ResourceModel\Program\Collection
     */
    private $programCollection;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * DeleteButton constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programCollection
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Affiliate\Model\ResourceModel\Program\Collection $programCollection,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->programCollection = $programCollection;
        $this->messageManager = $messageManager;
    }

    public function afterGetButtonData(
        \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\DeleteButton $subject,
        $result
    ) {
        $ruleId = $this->request->getParam('id');
        if ($this->programCollection->isProgramRule($ruleId)) {
            $this->messageManager->addNoticeMessage(
                __('This shopping cart price rule is assigned to the Affiliate Program. 
                The affiliate program discount will not apply without a rule assigned.')
            );
            $result = [];
        }
        return $result;
    }
}
