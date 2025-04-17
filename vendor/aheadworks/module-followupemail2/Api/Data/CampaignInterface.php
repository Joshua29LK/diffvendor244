<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CampaignInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface CampaignInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID            = 'id';
    const NAME          = 'name';
    const DESCRIPTION   = 'description';
    const START_DATE    = 'start_date';
    const END_DATE      = 'end_date';
    const STATUS        = 'status';
    /**#@-*/

    /**#@+
     * Status values
     */
    const STATUS_DISABLED   = 0;
    const STATUS_ENABLED    = 1;
    /**#@-*/

    /**
     * Get campaign ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set campaign ID
     *
     * @param int $campaignId
     * @return $this
     */
    public function setId($campaignId);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get start date
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set start date
     *
     * @param string|null $startDate
     * @return $this
     */
    public function setStartDate($startDate);

    /**
     * Get end date
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set end date
     *
     * @param string|null $endDate
     * @return $this
     */
    public function setEndDate($endDate);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\CampaignExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\CampaignExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Followupemail2\Api\Data\CampaignExtensionInterface $extensionAttributes
    );
}
