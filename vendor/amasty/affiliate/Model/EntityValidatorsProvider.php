<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Model;

class EntityValidatorsProvider
{
    /**
     * @var array
     */
    private $validators;

    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * Retrieve validators for entity
     *
     * @param string $entity
     *
     * @return \Zend_Validate
     */
    public function get(string $entity): \Zend_Validate
    {
        $entityValidators = new \Zend_Validate();

        if (isset($this->validators[$entity])) {
            foreach ($this->validators[$entity] as $validator) {
                if (!$validator instanceof \Zend_Validate_Interface) {
                    throw new \InvalidArgumentException(
                        sprintf('Entity validator mus implement %s', \Zend_Validate_Interface::class)
                    );
                }
                $entityValidators->addValidator($validator);
            }

        }

        return $entityValidators;
    }
}
