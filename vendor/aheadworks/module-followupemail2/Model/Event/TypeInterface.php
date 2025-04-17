<?php
namespace Aheadworks\Followupemail2\Model\Event;

/**
 * Interface TypeInterface
 * @package Aheadworks\Followupemail2\Model\Event
 */
interface TypeInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ENABLED               = 'enabled';
    const CODE                  = 'code';
    const TITLE                 = 'title';
    const DESCRIPTION           = 'description';
    const HANDLER               = 'handler';
    const CUSTOMER_CONDITIONS   = 'customer_conditions';
    const CART_CONDITIONS       = 'cart_conditions';
    const ORDER_CONDITIONS      = 'order_conditions';
    const PRODUCT_CONDITIONS    = 'product_conditions';
    const PRODUCT_RULES         = 'product_rules';
    const ALLOWED_FOR_GUESTS    = 'allowed_for_guests';
    const EMAIL_PREDICTION      = 'email_prediction';
    /**#@-*/

    /**
     * Check if type is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Get type code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get type title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get type description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get type handler
     *
     * @return HandlerInterface
     */
    public function getHandler();

    /**
     * Check if customer conditions is enabled
     *
     * @return bool
     */
    public function isCustomerConditionsEnabled();

    /**
     * Check if cart conditions is enabled
     *
     * @return bool
     */
    public function isCartConditionsEnabled();

    /**
     * Check if order conditions is enabled
     *
     * @return bool
     */
    public function isOrderConditionsEnabled();

    /**
     * Check if product conditions is enabled
     *
     * @return bool
     */
    public function isProductConditionsEnabled();

    /**
     * Check if product rules is enabled
     *
     * @return bool
     */
    public function isProductRulesEnabled();

    /**
     * Check if email prediction is enabled
     *
     * @return bool
     */
    public function isEmailPredictionEnabled();

    /**
     * Is element enabled
     *
     * @param string $element
     * @return bool
     */
    public function isElementEnabled($element);
}
