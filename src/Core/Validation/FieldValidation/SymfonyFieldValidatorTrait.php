<?php

namespace SilverStripe\Core\Validation\FieldValidation;

use LogicException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Core\Validation\ConstraintValidator;

/**
 * Trait for FieldValidators which validate using Symfony constraints
 */
trait SymfonyFieldValidatorTrait
{
    protected function validateValue(): ValidationResult
    {
        if (!is_a($this, SymfonyFieldValidatorInterface::class)) {
            $message = 'Classes using SymfonyFieldValidatorTrait must implement SymfonyFieldValidatorInterface';
            throw new LogicException($message);
        }
        $result = parent::validateValue();
        if (!$result->isValid()) {
            return $result;
        }
        $constraint = $this->getConstraint();
        $validationResult = ConstraintValidator::validate($this->value, $constraint, $this->name);
        return $result->combineAnd($validationResult);
    }
}
