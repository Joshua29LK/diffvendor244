<?php
namespace Aheadworks\Followupemail2\Model\Sample;

use Magento\Framework\Module\Dir\Reader as DirReader;

/**
 * Class SchemaLocator
 * @package Aheadworks\Followupemail2\Model\Sample
 * @codeCoverageIgnore
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var string
     */
    private $schema;

    /**
     * @var string
     */
    private $perFileSchema;

    /**
     * SchemaLocator constructor.
     * @param DirReader $moduleReader
     */
    public function __construct(
        DirReader $moduleReader
    ) {
        $this->schema = $moduleReader->getModuleDir('etc', 'Aheadworks_Followupemail2') .
            '/' . 'campaigns_sample_data.xsd';
        $this->perFileSchema = $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
