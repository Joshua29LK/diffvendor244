<?php

namespace Amasty\Affiliate\Block\Adminhtml\Customer\Tab;

use Amasty\Affiliate\Api\Data\AccountInterface;
use Magento\Backend\Block\Template;
use Magento\Customer\Controller\RegistryConstants;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    private $yesnoFactory;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        array $data = []
    ) {
        $this->yesnoFactory = $yesnoFactory;
        $this->accountRepository = $accountRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->getByCustomerId($this->getCustomerId());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setUseContainer(true);
        $form->setHtmlIdPrefix('user_');
        $fieldset = $form->addFieldset('amasty_affiliate_account_fieldset', ['legend' => __('Affiliate Account')]);
        $yesno = $this->yesnoFactory->create()->toOptionArray();

        $fieldset->addField(
            'is_affiliate_active',
            'select',
            [
                'name' => 'affiliate[is_affiliate_active]',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => $yesno,
                'data-form-part' => 'customer_form'
            ]
        );

        $fieldset->addField(
            'receive_notifications',
            'select',
            [
                'name' => 'affiliate[receive_notifications]',
                'label' => __('Receive Notifications'),
                'title' => __('Receive Notifications'),
                'values' => $yesno,
                'data-form-part' => 'customer_form'
            ]
        );

        $enablerJs = $this->getLayout()
            ->createBlock(Template::class)
            ->setTemplate('Amasty_Affiliate::form/element/account/js/enabler.phtml');
        $fieldset->addField(
            AccountInterface::IS_CUSTOM_REFERRING_CODE,
            'select',
            [
                'name' => 'affiliate[is_custom_referring_code]',
                'label' => __('Allow Setting Custom Affiliate Code'),
                'title' => __('Allow Setting Custom Affiliate Code'),
                'values' => $yesno,
                'data-form-part' => 'customer_form',
                'note' => __(
                    'Select ‘Yes’ if you’d like to set a custom affiliate code that will be used in the referral links.'
                    . ' Important! Referral links with the old affiliate code will become invalid.'
                ),
                'after_element_html' => $enablerJs->toHtml()
            ]
        );

        $fieldset->addField(
            AccountInterface::REFERRING_CODE,
            'text',
            [
                'name' => 'affiliate[referring_code]',
                'label' => __('Custom Affiliate Code'),
                'title' => __('Custom Affiliate Code'),
                'data-form-part' => 'customer_form',
                'note' => __('Please update the default affiliate code with the custom value.'),
                'required' => true,
                'class' => 'required-entry validate-alphanum',
                'disabled' => !$account->getIsCustomReferringCode()
            ]
        );

        $form->setValues($account->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
}
