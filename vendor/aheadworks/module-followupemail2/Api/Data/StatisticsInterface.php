<?php
namespace Aheadworks\Followupemail2\Api\Data;

/**
 * Interface StatisticsInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface StatisticsInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const SENT = 'sent';
    const OPENED = 'opened';
    const CLICKED = 'clicked';
    const OPEN_RATE = 'open_rate';
    const CLICK_RATE = 'click_rate';
    /**#@-*/

    /**
     * Get sent
     *
     * @return int
     */
    public function getSent();

    /**
     * Set sent
     *
     * @param int $sent
     * @return $this
     */
    public function setSent($sent);

    /**
     * Get opened
     *
     * @return int
     */
    public function getOpened();

    /**
     * Set opened
     *
     * @param int $opened
     * @return $this
     */
    public function setOpened($opened);

    /**
     * Get clicked
     *
     * @return int
     */
    public function getClicked();

    /**
     * Set clicked
     *
     * @param int $clicked
     * @return $this
     */
    public function setClicked($clicked);

    /**
     * Get open rate
     *
     * @return float
     */
    public function getOpenRate();

    /**
     * Set open rate
     *
     * @param float $openRate
     * @return $this
     */
    public function setOpenRate($openRate);

    /**
     * Get click rate
     *
     * @return float
     */
    public function getClickRate();

    /**
     * Set click rate
     *
     * @param float $clickRate
     * @return $this
     */
    public function setClickRate($clickRate);
}
