<?php
namespace Aheadworks\Followupemail2\Model\Template;

/**
 * Interface TransportBuilderInterface
 *
 * @package Aheadworks\Followupemail2\Model\Template
 */
interface TransportBuilderInterface
{
    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     * @return $this
     */
    public function setTemplateIdentifier($templateIdentifier);

    /**
     * Set template options
     *
     * @param array $templateOptions
     * @return $this
     */
    public function setTemplateOptions($templateOptions);

    /**
     * Set template vars
     *
     * @param array $templateVars
     * @return $this
     */
    public function setTemplateVars($templateVars);

    /**
     * Set mail from address
     *
     * @param string|array $from
     * @return $this
     */
    public function setFrom($from);

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     * @return $this
     */
    public function addTo($address, $name = '');

    /**
     * Get mail transport
     *
     * @return \Magento\Framework\Mail\TransportInterface
     */
    public function getTransport();
}
