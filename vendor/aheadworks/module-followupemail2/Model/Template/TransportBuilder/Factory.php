<?php
namespace Aheadworks\Followupemail2\Model\Template\TransportBuilder;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Aheadworks\Followupemail2\Model\Template\TransportBuilderInterface;

/**
 * Class Factory
 *
 * @package Aheadworks\Followupemail2\Model\Template\TransportBuilder
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ProductMetadataInterface $productMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Create email transport builder instance
     *
     * @return TransportBuilderInterface
     */
    public function create()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        $messageClassName = version_compare($magentoVersion, '2.3.3', '>=')
            ? Version233::class
            : VersionPriorTo233::class;

        return $this->objectManager->create($messageClassName);
    }
}
