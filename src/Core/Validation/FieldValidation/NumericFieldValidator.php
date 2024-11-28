<?php

namespace SilverStripe\Core\Validation\FieldValidation;

use InvalidArgumentException;
use SilverStripe\Core\Validation\ValidationResult;

/**
 * Validates that a value is a numeric value
 * Optionally, can also check that the value is within a certain range
 */
class NumericFieldValidator extends FieldValidator
{
    /**
     * Minimum size of the number
     */
    protected ?int $minValue;

    /**
     * Maximum size of the number
     */
    protected ?int $maxValue;

    public function __construct(
        string $name,
        mixed $value,
        ?int $minValue = null,
        ?int $maxValue = null,
    ) {
        if (!is_null($minValue) && !is_null($maxValue) && $maxValue < $minValue) {
            throw new InvalidArgumentException('maxValue cannot be less than minValue');
        }
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        parent::__construct($name, $value);
    }

    protected function validateValue(): ValidationResult
    {
        $result = ValidationResult::create();
        if (!is_numeric($this->value)) {
            $message = _t(__CLASS__ . '.NOTNUMERIC', 'Must be numeric');
            $result->addFieldError($this->name, $message);
        } elseif (!is_null($this->minValue) && $this->value < $this->minValue) {
            $result->addFieldError($this->name, $this->getTooSmallMessage());
        } elseif (!is_null($this->maxValue) && $this->value > $this->maxValue) {
            $result->addFieldError($this->name, $this->getTooLargeMessage());
        }
        return $result;
    }

    protected function getTooSmallMessage(): string
    {
        return _t(
            __CLASS__ . '.TOOSMALL',
            'Value cannot be less than {minValue}',
            ['minValue' => $this->minValue]
        );
    }

    protected function getTooLargeMessage(): string
    {
        return _t(
            __CLASS__ . '.TOOLARGE',
            'Value cannot be greater than {maxValue}',
            ['maxValue' => $this->maxValue]
        );
    }
}
