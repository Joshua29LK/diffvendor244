<?php
namespace Aheadworks\Followupemail2\Model\Statistics;

use Aheadworks\Followupemail2\Api\Data\StatisticsInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class Info
 * @package Aheadworks\Followupemail2\ModelStatistics
 * @codeCoverageIgnore
 */
class Info extends AbstractSimpleObject implements StatisticsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSent()
    {
        return $this->_get(self::SENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setSent($sent)
    {
        return $this->setData(self::SENT, $sent);
    }

    /**
     * {@inheritdoc}
     */
    public function getOpened()
    {
        return $this->_get(self::OPENED);
    }

    /**
     * {@inheritdoc}
     */
    public function setOpened($opened)
    {
        return $this->setData(self::OPENED, $opened);
    }

    /**
     * {@inheritdoc}
     */
    public function getClicked()
    {
        return $this->_get(self::CLICKED);
    }

    /**
     * {@inheritdoc}
     */
    public function setClicked($clicked)
    {
        return $this->setData(self::CLICKED, $clicked);
    }

    /**
     * {@inheritdoc}
     */
    public function getOpenRate()
    {
        return $this->_get(self::OPEN_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOpenRate($openRate)
    {
        return $this->setData(self::OPEN_RATE, $openRate);
    }

    /**
     * {@inheritdoc}
     */
    public function getClickRate()
    {
        return $this->_get(self::CLICK_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setClickRate($clickRate)
    {
        return $this->setData(self::CLICK_RATE, $clickRate);
    }
}
