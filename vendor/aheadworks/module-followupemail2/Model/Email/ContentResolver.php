<?php
namespace Aheadworks\Followupemail2\Model\Email;

use Aheadworks\Followupemail2\Api\Data\EmailContentInterface;
use Aheadworks\Followupemail2\Api\Data\EmailInterface;

/**
 * Class ContentResolver
 * @package Aheadworks\Followupemail2\Model\Email
 */
class ContentResolver
{
    /**#@+
     * Content index values
     */
    const INDEX_CONTENT_A   = 0;
    const INDEX_CONTENT_B   = 1;
    /**#@-*/

    /**
     * @param EmailInterface $email
     * @return EmailContentInterface
     */
    public function getCurrentContent($email)
    {
        /** @var EmailContentInterface[] $contentData */
        $contentData = $email->getContent();
        if ($email->getAbTestingMode()) {
            $content = $this->getAbContent($email->getAbEmailContent(), $contentData);
        } else {
            $content = $this->getPrimaryContent($email->getPrimaryEmailContent(), $contentData);
        }
        return $content;
    }

    /**
     * Get current A/B content
     *
     * @param int $contentVersion
     * @param EmailContentInterface[] $contentData
     * @return EmailContentInterface
     */
    private function getAbContent($contentVersion, $contentData)
    {
        if ($contentVersion) {
            if ($contentVersion == EmailInterface::CONTENT_VERSION_A) {
                $content = $contentData[self::INDEX_CONTENT_B];
            } else {
                $content = $contentData[self::INDEX_CONTENT_A];
            }
        } else {
            $content = $contentData[self::INDEX_CONTENT_A];
        }

        return $content;
    }

    /**
     * Get current A/B content version
     *
     * @param EmailInterface $email
     * @return int|false
     */
    public function getCurrentAbContentVersion($email)
    {
        $contentVersion = false;

        if ($email->getAbTestingMode()) {
            $contentVersion = $email->getAbEmailContent();
            if ($contentVersion) {
                if ($contentVersion == EmailInterface::CONTENT_VERSION_A) {
                    $contentVersion = EmailInterface::CONTENT_VERSION_B;
                } else {
                    $contentVersion = EmailInterface::CONTENT_VERSION_A;
                }
            } else {
                $contentVersion = EmailInterface::CONTENT_VERSION_A;
            }
        }

        return $contentVersion;
    }

    /**
     * Get primary content
     *
     * @param int $primaryContent
     * @param EmailContentInterface[] $contentData
     * @return EmailContentInterface
     */
    private function getPrimaryContent($primaryContent, $contentData)
    {
        if (!$primaryContent || $primaryContent == EmailInterface::CONTENT_VERSION_A) {
            $content = $contentData[self::INDEX_CONTENT_A];
        } else {
            $content = $contentData[self::INDEX_CONTENT_B];
        }
        return $content;
    }
}
