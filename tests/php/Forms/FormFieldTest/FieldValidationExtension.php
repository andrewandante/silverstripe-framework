<?php

namespace SilverStripe\Forms\Tests\FormFieldTest;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Core\Validation\ValidationResult;

class FieldValidationExtension extends Extension implements TestOnly
{
    protected bool $excludeFromValidation = false;

    protected bool $triggerTestValidationError = false;

    protected function updateValidate(ValidationResult &$result)
    {
        if ($this->excludeFromValidation) {
            $result = new ValidationResult();
        } elseif ($this->triggerTestValidationError) {
            $result->addFieldError($this->owner->getName(), 'A test error message');
        }
    }

    public function setExcludeFromValidation(bool $exclude)
    {
        $this->excludeFromValidation = $exclude;
    }

    public function setTriggerTestValidationError(bool $triggerTestValidationError)
    {
        $this->triggerTestValidationError = $triggerTestValidationError;
    }
}
