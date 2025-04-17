<?php

namespace Amasty\Affiliate\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Amasty\Affiliate\Model\Account;

class WidgetPost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * WidgetPost constructor.
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param Account $account
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        Account $account,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $context->getMessageManager();
        $this->account = $account;
        $this->accountRepository = $accountRepository;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            /** @var Account $account */
            $account = $this->accountRepository->getByCustomerId($this->customerSession->getCustomerId());
            $data = $this->prepareData($data);
            $account->addData($data);
            $this->accountRepository->save($account);
        }

        $this->messageManager->addSuccessMessage(__('Affiliate Widget has successfully saved'));

        return $resultRedirect->setPath('amasty_affiliate/account/widget');
    }

    protected function prepareData($data)
    {
        if (!key_exists('widget_show_price', $data)) {
            $data['widget_show_price'] = 0;
        }

        if (!key_exists('widget_show_name', $data)) {
            $data['widget_show_name'] = 0;
        }

        return $data;
    }
}
