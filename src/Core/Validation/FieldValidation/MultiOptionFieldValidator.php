<?php

namespace SilverStripe\Core\Validation\FieldValidation;

use SilverStripe\Core\Validation\ValidationResult;

/**
 * Validates that that all values are one of a set of options
 */
class MultiOptionFieldValidator extends OptionFieldValidator
{
    /**
     * @param mixed $value - an iterable set of values to be validated
     */
    public function __construct(string $name, mixed $value, array $options)
    {
        parent::__construct($name, $value, $options);
    }

    protected function validateValue(): ValidationResult
    {
        $result = ValidationResult::create();
        if (!is_iterable($this->value) && !is_null($this->value)) {
            $result->addFieldError($this->name, $this->getMessage());
            return $result;
        }
        foreach ($this->value as $value) {
            $this->checkValueInOptions($value, $result);
        }
        return $result;
    }
}
