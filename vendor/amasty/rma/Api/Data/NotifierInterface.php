<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Api\Data;

interface NotifierInterface
{
    public function notify(
        \Amasty\Rma\Api\Data\RequestInterface $request,
        \Amasty\Rma\Api\Data\MessageInterface $message
    ): void;
}
