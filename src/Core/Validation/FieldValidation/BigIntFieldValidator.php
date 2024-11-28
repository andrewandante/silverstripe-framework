<?php

namespace SilverStripe\Core\Validation\FieldValidation;

use RunTimeException;
use SilverStripe\Core\Validation\ValidationResult;

/**
 * A field validator for 64-bit integers
 * Will throw a RunTimeException if used on a 32-bit system
 */
class BigIntFieldValidator extends IntFieldValidator
{
    /**
     * The minimum value for a signed 64-bit integer.
     * Defined as string instead of int otherwise will end up as a float
     * on 64-bit systems
     *
     * When this is cast to an int in IntFieldValidator::__construct()
     * it will be properly cast to an int
     */
    protected const MIN_INT = '-9223372036854775808';

    /**
     * The maximum value for a signed 64-bit integer.
     */
    protected const MAX_INT = '9223372036854775807';

    public function __construct(
        string $name,
        mixed $value,
        ?int $minValue = null,
        ?int $maxValue = null
    ) {
        if (is_null($minValue) || is_null($maxValue)) {
            $bits = strlen(decbin(~0));
            if ($bits === 32) {
                throw new RunTimeException('Cannot use BigIntFieldValidator on a 32-bit system');
            }
        }
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        parent::__construct($name, $value, $minValue, $maxValue);
    }

    protected function validateValue(): ValidationResult
    {
        $result = ValidationResult::create();
        // Validate string values that are too large or too small
        // Only testing for string values here as that's all bccomp can take as arguments
        // int values that are too large or too small will be cast to float
        // on 64-bit systems and will fail the validation in IntFieldValidator
        if (is_string($this->value)) {
            if (!is_null($this->minValue) && bccomp($this->value, static::MIN_INT) === -1) {
                $result->addFieldError($this->name, $this->getTooSmallMessage());
            }
            if (!is_null($this->maxValue) && bccomp($this->value, static::MAX_INT) === 1) {
                $result->addFieldError($this->name, $this->getTooLargeMessage());
            }
        }
        $result->combineAnd(parent::validateValue());
        return $result;
    }
}
