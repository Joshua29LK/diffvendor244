<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Controller\Adminhtml\Widget;

use Amasty\Affiliate\Model\Rule\Condition\Affiliate;
use Amasty\Affiliate\Block\Adminhtml\Widget\Chooser\AffiliateCode;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Chooser extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Amasty_Affiliate::affiliate';
    public const ATTRIBUTE_PARAM = 'attribute';

    /**
     * Prepare block for chooser
     *
     * @return void
     */
    public function execute()
    {
        $attributeCode = $this->getRequest()->getParam(self::ATTRIBUTE_PARAM);

        if ($attributeCode == Affiliate::AFFILIATE_CODE_ATTR) {
            $block = $this->_view->getLayout()->createBlock(
                AffiliateCode::class,
                'amasty_affiliate_widget_chooser_affiliate_code',
                ['data' => ['js_form_object' => $this->getRequest()->getParam('form')]]
            );
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
