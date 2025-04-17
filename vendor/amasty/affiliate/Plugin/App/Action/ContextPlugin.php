<?php

namespace Amasty\Affiliate\Plugin\App\Action;

use Magento\Framework\App\RequestInterface;

class ContextPlugin
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Amasty\Affiliate\Model\Repository\AccountRepository
     */
    private $accountRepository;

    /**
     * ContextPlugin constructor.
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Amasty\Affiliate\Model\Repository\AccountRepository $accountRepository
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Amasty\Affiliate\Model\Repository\AccountRepository $accountRepository
    ) {
        $this->httpContext = $httpContext;
        $this->accountRepository = $accountRepository;
    }

    public function beforeDispatch(\Magento\Framework\App\ActionInterface $subject, RequestInterface $request)
    {
        $this->httpContext->setValue(
            'amasty_affiliate_account',
            $this->accountRepository->isAffiliate(),
            false
        );
    }
}
