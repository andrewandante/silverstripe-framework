<?php

namespace SilverStripe\Forms\FieldValidatorConverter;

use SilverStripe\Forms\FieldValidatorConverter\FieldValidatorConverterInterface;
use RuntimeException;

trait FieldValidatorConverterTrait
{
    /**
     * Returns a callable used by the FieldValidator to convert internally
     * stored values to a format used by the frontend.
     */
    public function getFieldValidatorConverter(): callable
    {
        if (!is_a($this, FieldValidatorConverterInterface::class)) {
            $message = get_class($this) . ' must implement interface ' . FieldValidatorConverterInterface::class;
            throw new RuntimeException($message);
        }
        return function (mixed $value) {
            return $this->internalToFrontend($value);
        };
    }
}
