<?php
namespace Aheadworks\Followupemail2\Model\Email\Address;

/**
 * Class EmailAddressProcessor
 * @package Aheadworks\Followupemail2\Model\Email\Address
 */
class EmailAddressProcessor
{
    /**
     * Convert to array bcc emails
     *
     * @param string|null $addresses
     * @return array
     */
    public function convertToArray($addresses)
    {
        $result = [];
        if (is_string($addresses) && $addresses != '') {
            $result = explode(',', $addresses ?? '');
            foreach ($result as &$address) {
                $address = trim($address);
            }
        }
        return $result;
    }
}
