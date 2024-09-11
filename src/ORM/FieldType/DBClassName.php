<?php

namespace SilverStripe\ORM\FieldType;

use SilverStripe\ORM\DB;

/**
 * Represents a classname selector, which respects obsolete clasess.
 */
class DBClassName extends DBEnum
{
    use DBClassNameTrait;

    public function requireField(): void
    {
        $parts = [
            'datatype' => 'enum',
            'enums' => $this->getEnumObsolete(),
            'character set' => 'utf8',
            'collate' => 'utf8_general_ci',
            'default' => $this->getDefault(),
            'table' => $this->getTable(),
            'arrayValue' => $this->arrayValue
        ];

        $values = [
            'type' => 'enum',
            'parts' => $parts
        ];

        DB::require_field($this->getTable(), $this->getName(), $values);
    }

    public function getDefault(): string
    {
        // Check for assigned default
        $default = parent::getDefault();
        if ($default) {
            return $default;
        }

        return $this->getDefaultClassName();
    }
}
