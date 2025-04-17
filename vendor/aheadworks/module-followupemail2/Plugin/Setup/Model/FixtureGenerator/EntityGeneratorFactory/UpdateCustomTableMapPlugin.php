<?php
namespace Aheadworks\Followupemail2\Plugin\Setup\Model\FixtureGenerator\EntityGeneratorFactory;

use Magento\Setup\Model\FixtureGenerator\EntityGenerator;
use Magento\Setup\Model\FixtureGenerator\EntityGeneratorFactory;

/**
 * Class UpdateCustomTableMapPlugin
 * @package Aheadworks\Followupemail2\Plugin\Setup\Model\FixtureGenerator\EntityGeneratorFactory
 * @codeCoverageIgnore
 */
class UpdateCustomTableMapPlugin
{
    /**
     * Inject aw_fue2_event_history table data to FixtureGenerator\EntityGeneratorFactory arguments.
     *
     * @param EntityGeneratorFactory $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCreate(
        EntityGeneratorFactory $subject,
        array $data
    ) {
        $data['customTableMap']['aw_fue2_event_history'] = [
            'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
            'handler' => null,
        ];

        return [$data];
    }
}
