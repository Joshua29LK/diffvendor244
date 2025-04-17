<?php
namespace Aheadworks\Followupemail2\Api\Data;

use Aheadworks\Followupemail2\Api\Data\UnsubscribeExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface UnsubscribeInterface
 * @package Aheadworks\Followupemail2\Api\Data
 * @api
 */
interface UnsubscribeInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID        = 'id';
    const STORE_ID  = 'store_id';
    const EMAIL     = 'email';
    const TYPE      = 'type';
    const VALUE     = 'value';
    /**#@-*/

    /**#@+
     * Type values
     */
    const TYPE_ALL          = 1;
    const TYPE_EVENT_TYPE   = 2;
    const TYPE_EVENT_ID     = 3;
    /**#@-*/

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int|null $Id
     * @return $this
     */
    public function setId($Id);

    /**
     * Get store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get type
     *
     * @return int
     */
    public function getType();

    /**
     * Set type
     *
     * @param int $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Followupemail2\Api\Data\UnsubscribeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Followupemail2\Api\Data\UnsubscribeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(UnsubscribeExtensionInterface $extensionAttributes);
}
