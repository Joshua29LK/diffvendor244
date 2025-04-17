<?php

namespace Amasty\Affiliate\Model\Account\ReferringCode;

use Amasty\Affiliate\Model\Account;
use Amasty\Affiliate\Model\ResourceModel\Validator\Account\ReferringCodeUnique;
use Magento\Framework\Validator\AbstractValidator;

class Validator extends AbstractValidator
{
    /**
     * Max allowed length of referring code value
     */
    public const REFERRING_CODE_MAX_LENGTH = 64;

    /**
     * @var ReferringCodeUnique
     */
    private $uniquenessValidator;

    /**
     * @var Account
     */
    private $context;

    public function __construct(ReferringCodeUnique $uniquenessValidator)
    {
        $this->uniquenessValidator = $uniquenessValidator;
    }

    /**
     * Returns true if and only if referring code value meets the validation requirements
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();

        if (empty($value)) {
            if ($this->context && $this->context->getIsCustomReferringCode()) {
                $this->_addMessages(['Referring Code is required.']);

                return false;
            }
        }

        if (strlen($value) > self::REFERRING_CODE_MAX_LENGTH) {
            $this->_addMessages(['Referring Code exceeds the allowed maximum length of \'64\'.']);

            return false;
        }

        if ($this->context) {
            $customerId = (int)$this->context->getCustomerId();
            if ($customerId && !$this->uniquenessValidator->isUnique((string)$value, $customerId)) {
                $this->_addMessages(['This affiliate code is already taken. Please choose another one.']);

                return false;
            }
        }

        return true;
    }

    /**
     * Set validation context
     *
     * @param Account $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }
}
