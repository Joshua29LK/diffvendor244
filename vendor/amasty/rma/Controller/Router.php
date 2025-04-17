<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    public const RMA_URL_SYSTEM_ROUTE = 'amasty_rma';

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Amasty\Rma\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * Router constructor.
     *
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Amasty\Rma\Model\ConfigProvider $configProvider
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Amasty\Rma\Model\ConfigProvider $configProvider
    ) {
        $this->actionFactory = $actionFactory;
        $this->configProvider = $configProvider;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ActionInterface|false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = explode(DIRECTORY_SEPARATOR, trim($request->getPathInfo(), DIRECTORY_SEPARATOR));
        $compareUrl = $this->getPathUrlFromSetting();

        if (isset($identifier[0]) && ($compareUrl == $identifier[0])) {
            $newPathInfo = str_replace(
                '/' . $compareUrl . '/',
                '/' . self::RMA_URL_SYSTEM_ROUTE . '/',
                $request->getPathInfo()
            );

            $request->setPathInfo($newPathInfo);

            return $this->actionFactory->create(
                \Magento\Framework\App\Action\Forward::class,
                ['request' => $request]
            );
        }

        return false;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPathUrlFromSetting()
    {
        return trim(
            $this->configProvider->getUrlPrefix(),
            DIRECTORY_SEPARATOR
        ) ? : "rma";
    }
}
