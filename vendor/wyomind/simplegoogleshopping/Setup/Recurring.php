<?php
/**
 * Copyright © 2022 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\SimpleGoogleShopping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Wyomind\Framework\Helper\Install;
use Wyomind\Framework\Helper\License\UpdateFactory;

class Recurring implements InstallSchemaInterface
{

    /**
     * @var UpdateFactory
     */
    protected $license;
    /**
     * @var Install
     */
    private $framework = null;

    /**
     * @param Install $framework
     * @param UpdateFactory $license
     */
    public function __construct(
        Install       $framework,
        UpdateFactory $license
    ) {
        $this->framework = $framework;
        $this->license = $license;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface   $setup,
        ModuleContextInterface $context
    ) {
        $files = ['Model/ResourceModel/Product/Collection.php'];
        $this->framework->copyFilesByMagentoVersion(__FILE__, $files);

        if ($context->getVersion() != null) {
            $this->license->create()->update(__CLASS__, $context);
        }

    }
}
