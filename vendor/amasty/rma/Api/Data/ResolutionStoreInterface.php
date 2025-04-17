<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_Rma
*/


namespace Amasty\Rma\Api\Data;

/**
 * Interface ResolutionStoreInterface
 */
interface ResolutionStoreInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const RESOLUTION_STORE_ID = 'resolution_store_id';
    public const RESOLUTION_ID = 'resolution_id';
    public const STORE_ID = 'store_id';
    public const LABEL = 'label';
    /**#@-*/

    /**
     * @param int $resolutionStoreId
     *
     * @return \Amasty\Rma\Api\Data\ResolutionStoreInterface
     */
    public function setResolutionStoreId($resolutionStoreId);

    /**
     * @return int
     */
    public function getResolutionStoreId();

    /**
     * @param int $resolutionId
     *
     * @return \Amasty\Rma\Api\Data\ResolutionStoreInterface
     */
    public function setResolutionId($resolutionId);

    /**
     * @return int
     */
    public function getResolutionId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\Rma\Api\Data\ResolutionStoreInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param string $label
     *
     * @return \Amasty\Rma\Api\Data\ResolutionStoreInterface
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getLabel();
}
