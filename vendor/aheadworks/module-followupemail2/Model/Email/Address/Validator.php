<?php
namespace Aheadworks\Followupemail2\Model\Email\Address;

use Magento\Framework\Validator\EmailAddress as EmailValidator;

/**
 * Class Validator
 * @package Aheadworks\Followupemail2\Model\Email\Address
 */
class Validator
{
    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var EmailAddressProcessor
     */
    private $emailAddressProcessor;

    /**
     * @param EmailValidator $emailValidator
     * @param EmailAddressProcessor $emailAddressProcessor
     */
    public function __construct(
        EmailValidator $emailValidator,
        EmailAddressProcessor $emailAddressProcessor
    ) {
        $this->emailValidator = $emailValidator;
        $this->emailAddressProcessor = $emailAddressProcessor;
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @return bool
     */
    public function validateEmail($email)
    {
        return $this->emailValidator->isValid($email);
    }

    /**
     * Retrieve valid emails address
     *
     * @param array $emails
     * @return array
     */
    public function getValidEmails($emails)
    {
        $validEmails = [];
        if (count($emails) > 0) {
            foreach ($emails as $email) {
                if ($this->emailValidator->isValid($email)) {
                    $validEmails[] = $email;
                }
            }
        }

        return $validEmails;
    }
}
