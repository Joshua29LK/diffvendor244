<?php
namespace Aheadworks\Followupemail2\Model\Email\Variables;

use Magento\Framework\ObjectManagerInterface;
use Magento\Variable\Model\VariableFactory as EmailVariablesFactory;

/**
 * Class Resolver
 * @package Aheadworks\Followupemail2\Model\Email\Variables
 */
class Resolver
{
    /**#@+
     * Variables source model classes
     */
    // @codingStandardsIgnoreStart
    const EMAIL_VARIABLES_SOURCE_MODEL = 'Magento\Email\Model\Source\Variables';
    const VARIABLES_SOURCE_MODEL = 'Magento\Variable\Model\Source\Variables';
    // @codingStandardsIgnoreEnd
    /**#@-*/

    /**#@+
     * Variables js source
     */
    const NEW_VARIABLES_JS = 'Magento_Email/js/variables';
    const OLD_VARIABLES_JS = 'Magento_Variable/variables';
    /**#@-*/

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EmailVariablesFactory
     */
    private $emailVariablesFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EmailVariablesFactory $emailVariablesFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EmailVariablesFactory $emailVariablesFactory
    ) {
        $this->objectManager = $objectManager;
        $this->emailVariablesFactory = $emailVariablesFactory;
    }

    /**
     * Retrieve email variables
     *
     * @return array
     */
    public function getEmailVariables()
    {
        $variables = [];
        $customVariables = $this->emailVariablesFactory->create()->getVariablesOptionArray(true);

        if (class_exists(self::VARIABLES_SOURCE_MODEL)) {
            $sourceModel = $this->objectManager->get(self::VARIABLES_SOURCE_MODEL);
            $variables = array_merge($variables, $customVariables, $sourceModel->toOptionArray(true));
        } elseif (class_exists(self::EMAIL_VARIABLES_SOURCE_MODEL)) {
            $sourceModel = $this->objectManager->get(self::EMAIL_VARIABLES_SOURCE_MODEL);
            $variables[] = $customVariables;
            $variables[] = $sourceModel->toOptionArray(true);
        }

        return $variables;
    }

    /**
     * Retrieve variables js source
     *
     * @return string
     */
    public function getVariableJsSource()
    {
        $result = '';

        if (class_exists(self::VARIABLES_SOURCE_MODEL)) {
            $result = self::NEW_VARIABLES_JS;
        } elseif (class_exists(self::EMAIL_VARIABLES_SOURCE_MODEL)) {
            $result = self::OLD_VARIABLES_JS;
        }

        return $result;
    }
}
