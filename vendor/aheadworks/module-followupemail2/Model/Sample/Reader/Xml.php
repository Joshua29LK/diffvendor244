<?php
namespace Aheadworks\Followupemail2\Model\Sample\Reader;

use Aheadworks\Followupemail2\Model\Sample\Converter\Xml as SampleXmlConverter;
use Aheadworks\Followupemail2\Model\Sample\SchemaLocator as SampleSchemaLocator;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Config\Dom as ConfigDom;

/**
 * Class Xml
 * @package Aheadworks\Followupemail2\Model\Sample\Reader
 * @codeCoverageIgnore
 */
class Xml extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @param FileResolverInterface $fileResolver
     * @param SampleXmlConverter $converter
     * @param SampleSchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        SampleXmlConverter $converter,
        SampleSchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = 'campaigns_sample_data.xml',
        $idAttributes = [],
        $domDocumentClass = ConfigDom::class,
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
