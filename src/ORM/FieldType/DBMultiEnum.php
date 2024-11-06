<?php

namespace SilverStripe\ORM\FieldType;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Validation\FieldValidation\OptionFieldValidator;
use SilverStripe\Core\Validation\FieldValidation\MultiOptionFieldValidator;
use SilverStripe\Core\Validation\FieldValidation\StringFieldValidator;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\MultiSelectField;
use SilverStripe\ORM\Connect\MySQLDatabase;
use SilverStripe\ORM\DB;

/**
 * Represents an multi-select enumeration field.
 */
class DBMultiEnum extends DBEnum
{
    private static array $field_validators = [
        // disable parent field validators
        StringFieldValidator::class => null,
        OptionFieldValidator::class => null,
        // enable multi enum field validator
        MultiOptionFieldValidator::class => ['getEnum'],
    ];

    public function __construct($name = null, $enum = null, $default = null)
    {
        // MultiEnum needs to take care of its own defaults
        parent::__construct($name, $enum, null);

        // Validate and assign the default
        $this->default = null;
        if ($default) {
            $defaults = preg_split('/ *, */', trim($default ?? ''));
            foreach ($defaults as $thisDefault) {
                if (!in_array($thisDefault, $this->enum ?? [])) {
                    throw new \InvalidArgumentException(
                        "Enum::__construct() The default value '$thisDefault' does not match "
                        . 'any item in the enumeration'
                    );
                }
            }
            $this->default = implode(',', $defaults);
        }
    }

    public function getValueForValidation(): mixed
    {
        $value = parent::getValueForValidation();
        if (is_iterable($value)) {
            return $value;
        }
        if (is_string($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function requireField(): void
    {
        $charset = Config::inst()->get(MySQLDatabase::class, 'charset');
        $collation = Config::inst()->get(MySQLDatabase::class, 'collation');
        $values = [
            'type' => 'set',
            'parts' => [
                'enums' => $this->enum,
                'character set' => $charset,
                'collate' => $collation,
                'default' => $this->default,
                'table' => $this->tableName,
                'arrayValue' => $this->arrayValue,
            ],
        ];

        DB::require_field($this->tableName, $this->name, $values);
    }


    /**
     * Return a form field suitable for editing this field
     */
    public function formField(
        ?string $title = null,
        ?string $name = null,
        bool $hasEmpty = false,
        ?string $value = '',
        ?string $emptyString = null
    ): MultiSelectField {
        if (!$title) {
            $title = $this->name;
        }
        if (!$name) {
            $name = $this->name;
        }

        return CheckboxSetField::create($name, $title, $this->enumValues($hasEmpty), $value);
    }
}
