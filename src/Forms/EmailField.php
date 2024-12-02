<?php

namespace SilverStripe\Forms;

use SilverStripe\Core\Validation\FieldValidation\EmailFieldValidator;

/**
 * Text input field with validation for correct email format according to the relevant RFC.
 */
class EmailField extends TextField
{
    private static array $field_validators = [
        EmailFieldValidator::class,
    ];

    protected $inputType = 'email';

    public function Type()
    {
        return 'email text';
    }

    public function getSchemaValidation()
    {
        $rules = parent::getSchemaValidation();
        $rules['email'] = true;
        return $rules;
    }
}
