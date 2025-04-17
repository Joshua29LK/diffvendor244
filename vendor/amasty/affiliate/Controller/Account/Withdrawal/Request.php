<?php

namespace Amasty\Affiliate\Controller\Account\Withdrawal;

use Amasty\Affiliate\Api\AccountRepositoryInterface;
use Amasty\Affiliate\Api\WithdrawalRepositoryInterface;
use Amasty\Affiliate\Model\PriceConverter;
use Amasty\Affiliate\Model\ResourceModel\Withdrawal\CollectionFactory;
use Amasty\Affiliate\Model\Url;
use Amasty\Affiliate\Model\Withdrawal;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Request extends \Amasty\Affiliate\Controller\Account\Withdrawal\AbstractWithdrawal
{
    /**
     * @var PriceConverter
     */
    private $priceConverter;

    public function __construct(
        Context $context,
        Withdrawal $withdrawal,
        AccountRepositoryInterface $accountRepository,
        ScopeConfigInterface $scopeConfig,
        WithdrawalRepositoryInterface $withdrawalRepository,
        CollectionFactory $withdrawalCollectionFactory,
        Url $url,
        PriceConverter $priceConverter,
        Session $customerSession
    ) {
        $this->priceConverter = $priceConverter;
        parent::__construct(
            $context,
            $withdrawal,
            $accountRepository,
            $scopeConfig,
            $withdrawalRepository,
            $withdrawalCollectionFactory,
            $url,
            $priceConverter,
            $customerSession
        );
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $requestedAmount = $this->getRequest()->getParam('amount');
            $requestedAmount = $this->priceConverter->convertPriceToBaseCurrency($requestedAmount);

            if (!$this->validateWithdrawal($requestedAmount)) {
                return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
            }
            $this->withdrawal->create($requestedAmount);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
        }

        $this->messageManager->addSuccessMessage(__('Withdrawal was successfully created.'));

        return $resultRedirect->setPath($this->url->getPath('account/withdrawal'));
    }
}
