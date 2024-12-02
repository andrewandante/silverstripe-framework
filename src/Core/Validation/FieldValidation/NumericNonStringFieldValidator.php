<?php

namespace SilverStripe\Core\Validation\FieldValidation;

use SilverStripe\Core\Validation\ValidationResult;

/**
 * Validates that a value is a numeric value and not a string
 */
class NumericNonStringFieldValidator extends NumericFieldValidator
{
    protected function validateValue(): ValidationResult
    {
        $result = parent::validateValue();
        if (is_numeric($this->value) && is_string($this->value)) {
            $message = _t(__CLASS__ . '.WRONGTYPE', 'Must be numeric and not a string');
            $result->addFieldError($this->name, $message);
        }
        return $result;
    }
}
