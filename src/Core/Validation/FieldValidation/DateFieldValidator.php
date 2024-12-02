<?php

namespace SilverStripe\Core\Validation\FieldValidation;

use InvalidArgumentException;
use SilverStripe\Core\Validation\ValidationResult;

/**
 * Validates that a value is a valid date, which means that it follows the equivalent formats:
 * - PHP date format Y-m-d
 * - ISO format y-MM-dd i.e. DBDate::ISO_DATE
 *
 * Blank string values are allowed
 */
class DateFieldValidator extends StringFieldValidator
{
    /**
     * Minimum value as a date string
     */
    private ?string $minValue = null;

    /**
     * Minimum value as a unix timestamp
     */
    private ?int $minTimestamp = null;

    /**
     * Maximum value as a date string
     */
    private ?string $maxValue = null;

    /**
     * Maximum value as a unix timestamp
     */
    private ?int $maxTimestamp = null;

    /**
     * Converter to convert date strings to another format for display in error messages
     *
     * @var callable
     */
    private $converter;

    public function __construct(
        string $name,
        mixed $value,
        ?string $minValue = null,
        ?string $maxValue = null,
        ?callable $converter = null,
    ) {
        // Convert Y-m-d args to timestamps
        if (!is_null($minValue)) {
            $this->minTimestamp = $this->dateToTimestamp($minValue);
        }
        if (!is_null($maxValue)) {
            $this->maxTimestamp = $this->dateToTimestamp($maxValue);
        }
        if (!is_null($this->minTimestamp) && !is_null($this->maxTimestamp)
            && $this->maxTimestamp < $this->minTimestamp
        ) {
            throw new InvalidArgumentException('maxValue cannot be less than minValue');
        }
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->converter = $converter;
        parent::__construct($name, $value);
    }

    protected function validateValue(): ValidationResult
    {
        $result = parent::validateValue();
        // If the value is not a string, we can't validate it as a date
        if (!$result->isValid()) {
            return $result;
        }
        // Validate value is a valid date
        $timestamp = $this->dateToTimestamp($this->value ?? '');
        if ($timestamp === false) {
            $result->addFieldError($this->name, $this->getMessage());
            return $result;
        }
        // Validate value is within range
        if (!is_null($this->minTimestamp) && $timestamp < $this->minTimestamp) {
            $minValue = $this->minValue;
            if (!is_null($this->converter)) {
                $minValue = call_user_func($this->converter, $this->minValue) ?: $this->minValue;
            }
            $message = _t(
                __CLASS__ . '.TOOSMALL',
                'Value cannot be older than {minValue}',
                ['minValue' => $minValue]
            );
            $result->addFieldError($this->name, $message);
        } elseif (!is_null($this->maxTimestamp) && $timestamp > $this->maxTimestamp) {
            $maxValue = $this->maxValue;
            if (!is_null($this->converter)) {
                $maxValue = call_user_func($this->converter, $this->maxValue) ?: $this->maxValue;
            }
            $message = _t(
                __CLASS__ . '.TOOLARGE',
                'Value cannot be newer than {maxValue}',
                ['maxValue' => $maxValue]
            );
            $result->addFieldError($this->name, $message);
        }
        return $result;
    }

    protected function getFormat(): string
    {
        return 'Y-m-d';
    }

    protected function getMessage(): string
    {
        return _t(__CLASS__ . '.INVALID', 'Invalid date');
    }

    /**
     * Parse a date string into a unix timestamp using the format specified by getFormat()
     */
    private function dateToTimestamp(string $date): int|false
    {
        // Not using symfony/validator because it was allowing d-m-Y format strings
        $date = date_parse_from_format($this->getFormat(), $date);
        if ($date === false || $date['error_count'] > 0 || $date['warning_count'] > 0) {
            return false;
        }
        return mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
    }
}
