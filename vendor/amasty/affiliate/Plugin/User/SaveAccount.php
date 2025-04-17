<?php

namespace Amasty\Affiliate\Plugin\User;

use Amasty\Affiliate\Api\Data\AccountInterface;
use Magento\Framework\Api\DataObjectHelper;

class SaveAccount
{
    public const AFFILIATE_PARAMS_NAMESPACE = 'affiliate';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \Amasty\Affiliate\Model\AccountFactory
     */
    private $accountFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Model\AccountFactory $accountFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterDelete(
        $subject,
        $result
    ) {
        $subjectId = $subject->getId();
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->getByCustomerId($subjectId);
        $this->accountRepository->delete($account);

        return $result;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterSave($subject, $result)
    {
        $data = $this->request->getParam(self::AFFILIATE_PARAMS_NAMESPACE, []);
        $account = $this->accountRepository->getByCustomerId($subject->getId());
        if (!$account->getId()) {
            $account->setCustomerId($subject->getId());
        }

        if ($account->getId()) {
            $this->dataObjectHelper->populateWithArray(
                $account,
                $data,
                AccountInterface::class
            );
            $account->save();
        } elseif (!empty($data['is_affiliate_active']) || !empty($data['receive_notifications'])) {
            $this->messageManager->addErrorMessage(
                __('Affiliate Account changes were not saved: customer have to accept Affiliate Program\'s '
                . 'first in Customer Account.')
            );
        }

        return $result;
    }
}
