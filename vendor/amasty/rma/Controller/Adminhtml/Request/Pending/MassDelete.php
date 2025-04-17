<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/

declare(strict_types=1);

namespace Amasty\Rma\Controller\Adminhtml\Request\Pending;

use Amasty\Rma\Controller\Adminhtml\Request\AbstractMassDelete;

class MassDelete extends AbstractMassDelete
{
    public const ADMIN_RESOURCE = 'Amasty_Rma::pending_delete';
}
