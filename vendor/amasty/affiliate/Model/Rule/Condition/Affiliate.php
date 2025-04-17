<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model\Rule\Condition;

use Amasty\Affiliate\Model\Rule\Validator\Affiliate as AffiliateValidator;
use Amasty\Segments\Model\GuestCustomerData;
use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class Affiliate extends AbstractCondition
{
    public const AFFILIATE_CODE_ATTR = 'referring_code';

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var AffiliateValidator
     */
    private $affiliateValidator;

    public function __construct(
        Context $context,
        UrlInterface $url,
        AffiliateValidator $affiliateValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->url = $url;
        $this->affiliateValidator = $affiliateValidator;
    }

    /**
     * Get attribute options as new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions(): array
    {
        $attributes = [];
        foreach ($this->loadAttributeOptions()->getAttributeOption() as $attrCode => $attrLabel) {
            $attributes[] = [
                'value' => Affiliate::class . '|' . $attrCode,
                'label' => $attrLabel,
            ];
        }

        return $attributes;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions(): AbstractCondition
    {
        $this->setAttributeOption([self::AFFILIATE_CODE_ATTR => __('Used Custom Affiliate Code')]);

        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions(): AbstractCondition
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '()' => __('is one of'),
                '{}' => __('contains'),
                '!{}' => __('does not contain')
            ]
        );

        return $this;
    }

    /**
     * Retrieve attribute element as text
     *
     * @return Affiliate|AbstractElement
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * Retrieve chooser element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml(): string
    {
        return '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="'
            . $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif')
            . '" alt="" class="v-middle rule-chooser-trigger" title="'
            . __('Open Chooser') . '" /></a>';
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl(): string
    {
        $url = 'amasty_affiliate/widget/chooser/attribute/' . $this->getAttribute();
        if ($this->getJsFormObject()) {
            $url .= '/form/' . $this->getJsFormObject();
        }

        return $this->url->getUrl($url);
    }

    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply(): bool
    {
        return true;
    }

    /**
     * Present selected values as array
     *
     * @return array
     */
    public function getValueParsed(): array
    {
        $value = array_filter(array_map('trim', explode(',', $this->getValue())));
        if (in_array($this->getOperator(), ['{}', '!{}'])) {
            $value = array_map(function ($item) {
                return '%' . $item . '%';
            }, $value);
        }

        return $value;
    }

    /**
     * Validate model.
     *
     * @param AbstractModel $model
     * @return bool
     * @throws InputException
     */
    public function validate(AbstractModel $model): bool
    {
        if (!$this->isObjectValid($model)) {
            return false;
        }

        return $this->affiliateValidator->validate($this, $model);
    }

    /**
     * Validates type of object
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isObjectValid(AbstractModel $object): bool
    {
        if ($object instanceof Customer || $object instanceof GuestCustomerData) {
            return true;
        }

        return false;
    }
}
