<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CABAV
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CABAV\Model\Indexer;

class AlsoBought implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const BSS_CABAV_INDEXER_UPDATE_ALL = 'indexer_update_all';
    const BSS_CABAV_INDEXER_UPDATE_ON_SAVE = 'indexer_update_on_save';
    const BSS_CABAV_INDEXER_UPDATE_SCHEDULE = 'indexer_update_on_schedule';

    /**
     * @var \Bss\CABAV\Model\ResourceModel\ConnectionDB
     */
    protected $connectionDB;

    /**
     * AlsoBought constructor.
     * @param \Bss\CABAV\Model\ResourceModel\ConnectionDB $connectionDB
     */
    public function __construct(
        \Bss\CABAV\Model\ResourceModel\ConnectionDB $connectionDB
    ) {
        $this->connectionDB = $connectionDB;
    }

    /**
     * Used by mview, allows process indexer in the "Update on schedule" mode
     *
     * @param int[] $ids
     */
    public function execute($ids)
    {
        //Used by mview, allows you to process multiple placed orders in the "Update on schedule" mode
        $this->connectionDB->reindexData($ids, self::BSS_CABAV_INDEXER_UPDATE_SCHEDULE);
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull()
    {
        $this->connectionDB->reindexData(false, self::BSS_CABAV_INDEXER_UPDATE_ALL);
    }

    /**
     * Works with a set of entity changed (may be massaction)
     *
     * @param array $ids
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeList(array $ids)
    {
        //Works with a set of placed orders (mass actions and so on)
        return;
    }

    /**
     * Works in runtime for a single entity using plugins
     *
     * @param int $data
     */
    public function executeRow($data)
    {
        $this->connectionDB->reindexData($data, self::BSS_CABAV_INDEXER_UPDATE_ON_SAVE);
    }
}