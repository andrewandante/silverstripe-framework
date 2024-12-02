<?php

namespace SilverStripe\Forms;

/**
 * Validates the internal state of all fields in the form.
 */
class FieldsValidator extends Validator
{
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
