<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller\Guest;

use Amasty\Rma\Model\ConfigProvider;
use Amasty\Rma\Model\Cookie\HashChecker;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Logout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var HashChecker
     */
    private $hashChecker;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        HashChecker $hashChecker,
        ConfigProvider $configProvider,
        Context $context
    ) {
        parent::__construct($context);
        $this->hashChecker = $hashChecker;
        $this->configProvider = $configProvider;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $this->hashChecker->removeHash();

        return $this->resultRedirectFactory->create()->setPath(
            $this->configProvider->getUrlPrefix() . '/guest/login'
        );
    }
}
