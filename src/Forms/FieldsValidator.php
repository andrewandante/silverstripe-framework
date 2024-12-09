<?php

namespace SilverStripe\Forms;

use SilverStripe\Dev\Deprecation;

/**
 * Validates the internal state of all fields in the form.
 *
 * @deprecated 5.4.0 Will be replaced with functionality inside SilverStripe\Forms\Form::validate()
 */
class FieldsValidator extends Validator
{
    public function __construct()
    {
        Deprecation::noticeWithNoReplacment(
            '5.4.0',
            'Will be replaced with functionality inside SilverStripe\Forms\Form::validate()',
            Deprecation::SCOPE_CLASS
        );
        parent::__construct();
    }

    public function php($data): bool
    {
        $fields = $this->form->Fields();
        foreach ($fields as $field) {
            $this->result->combineAnd($field->validate());
        }
        return $this->result->isValid();
    }

    public function canBeCached(): bool
    {
        return true;
    }
}
