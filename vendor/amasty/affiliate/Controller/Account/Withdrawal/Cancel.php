<?php

namespace Amasty\Affiliate\Controller\Account\Withdrawal;

use Magento\Framework\App\Action\Context;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Amasty\Affiliate\Api\WithdrawalRepositoryInterface
     */
    private $withdrawalRepository;
    /**
     * @var \Amasty\Affiliate\Model\Url
     */
    private $url;

    public function __construct(
        Context $context,
        \Amasty\Affiliate\Api\WithdrawalRepositoryInterface $withdrawalRepository,
        \Amasty\Affiliate\Model\Url $url
    ) {
        $this->withdrawalRepository = $withdrawalRepository;
        parent::__construct($context);
        $this->url = $url;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('withdrawal_id');
        /** @var \Amasty\Affiliate\Model\Withdrawal $withdrawal */
        $withdrawal = $this->withdrawalRepository->get($id);
        $withdrawal->setStatus($withdrawal::STATUS_CANCELED);
        $this->withdrawalRepository->save($withdrawal);

        $this->messageManager->addSuccessMessage(__('Withdrawal was successfully canceled.'));

        return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
    }
}
