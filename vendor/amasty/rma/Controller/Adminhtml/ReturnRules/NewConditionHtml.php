<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Adminhtml\ReturnRules;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Backend\App\Action;
use Amasty\Rma\Controller\Adminhtml\AbstractReturnRules;
use Amasty\Rma\Api\Data\ReturnRulesInterfaceFactory;

class NewConditionHtml extends AbstractReturnRules
{
    /**
     * @var ReturnRulesInterfaceFactory
     */
    private $ruleFactory;

    public function __construct(Action\Context $context, ReturnRulesInterfaceFactory $ruleFactory)
    {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Generate Condition HTML form. Ajax
     */
    public function execute()
    {
        //for condition id in formats 1--1, not format to int
        $conditionId = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getPost('type')));
        $type = $typeArr[0];

        if (empty($type)) {
            return;
        }
        $model = $this->_objectManager->create($type)
            ->setId($conditionId)
            ->setType($type)
            ->setRule($this->ruleFactory->create())
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($this->getRequest()->getParam('form_namespace'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
