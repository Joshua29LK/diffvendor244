<?php

declare(strict_types=1);

namespace Amasty\ShopbyBase\Model\Detection;

use Magento\Framework\HTTP\Header;
use Magento\Framework\ObjectManagerInterface;

class MobileDetect
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Detection\MobileDetect|null
     */
    private $mobileDetector = null;

    /**
     * @var Header
     */
    private $httpHeader;

    public function __construct(
        Header $httpHeader,
        ObjectManagerInterface $objectManager
    ) {
        $this->httpHeader = $httpHeader;
        $this->objectManager = $objectManager;

        // We are using object manager to create 3rd-party packages' class
        if (class_exists(\Detection\MobileDetect::class)) {
            $this->mobileDetector = $this->objectManager->create(\Detection\MobileDetect::class);
        }
    }

    public function isMobile(): bool
    {
        return $this->mobileDetector === null
            ? stristr($this->httpHeader->getHttpUserAgent(), 'mobi') !== false
            : $this->mobileDetector->isMobile();
    }
}
