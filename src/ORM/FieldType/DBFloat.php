<?php

namespace SilverStripe\ORM\FieldType;

use SilverStripe\Core\Validation\FieldValidation\NumericNonStringFieldValidator;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DB;
use SilverStripe\Model\ModelData;

/**
 * Represents a floating point field.
 */
class DBFloat extends DBField
{
    private static array $field_validators = [
        NumericNonStringFieldValidator::class,
    ];

    public function __construct(?string $name = null, float|int $defaultVal = 0)
    {
        $this->setDefaultValue((float) $defaultVal);
        parent::__construct($name);
    }

    public function requireField(): void
    {
        $parts = [
            'datatype' => 'float',
            'null' => 'not null',
            'default' => $this->getDefaultValue(),
            'arrayValue' => $this->arrayValue
        ];
        $values = ['type' => 'float', 'parts' => $parts];
        DB::require_field($this->tableName, $this->name, $values);
    }

    public function setValue(mixed $value, null|array|ModelData $record = null, bool $markChanged = true): static
    {
        // Cast ints and numeric strings to floats
        if (is_int($value) || (is_string($value) && is_numeric($value))) {
            $value = (float) $value;
        }
        parent::setValue($value, $record, $markChanged);
        return $this;
    }

    /**
     * Returns the number, with commas and decimal places as appropriate, eg “1,000.00”.
     *
     * @uses number_format()
     */
    public function Nice(): string
    {
        return number_format($this->value ?? 0.0, 2);
    }

    public function Round($precision = 3): float
    {
        return round($this->value ?? 0.0, $precision ?? 0);
    }

    public function NiceRound($precision = 3): string
    {
        return number_format(round($this->value ?? 0.0, $precision ?? 0), $precision ?? 0);
    }

    public function scaffoldFormField(?string $title = null, array $params = []): ?FormField
    {
        $field = NumericField::create($this->name, $title);
        $field->setScale(null); // remove no-decimal restriction
        return $field;
    }

    public function nullValue(): ?float
    {
        return 0.0;
    }

    public function prepValueForDB(mixed $value): array|float|int|null
    {
        if ($value === true) {
            return 1;
        }

        if (empty($value) || !is_numeric($value)) {
            return 0;
        }

        return $value;
    }
}
