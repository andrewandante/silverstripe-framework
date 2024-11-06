<?php

namespace SilverStripe\ORM\FieldType;

use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\FormField;
use SilverStripe\Model\ModelData;

/**
 * Represents a decimal field containing a currency amount.
 * The currency class only supports single currencies.  For multi-currency support, use {@link Money}
 *
 *
 * Example definition via {@link DataObject::$db}:
 * <code>
 * static $db = array(
 *  "Price" => "Currency",
 *  "Tax" => "Currency(5)",
 * );
 * </code>
 */
class DBCurrency extends DBDecimal
{
    /**
     * The symbol that represents the currency
     */
    private static string $currency_symbol = '$';

    /**
     * Returns the number as a currency, eg “$1,000.00”.
     */
    public function Nice(): string
    {
        $val = static::config()->get('currency_symbol') . number_format(abs($this->value ?? 0.0) ?? 0.0, 2);
        if ($this->value < 0) {
            return "($val)";
        }

        return $val;
    }

    /**
     * Returns the number as a whole-number currency, eg “$1,000”.
     */
    public function Whole(): string
    {
        $val = static::config()->get('currency_symbol') . number_format(abs($this->value ?? 0.0) ?? 0.0, 0);
        if ($this->value < 0) {
            return "($val)";
        }
        return $val;
    }

    public function setValue(mixed $value, null|array|ModelData $record = null, bool $markChanged = true): static
    {
        if (is_string($value)) {
            $symbol = static::config()->get('currency_symbol');
            $val = str_replace(['$', ',', $symbol], '', $value);
            if (is_numeric($val)) {
                $value = (float) $val;
            }
        }
        parent::setValue($value, $record, $markChanged);
        return $this;
    }

    public function scaffoldFormField(?string $title = null, array $params = []): ?FormField
    {
        return CurrencyField::create($this->getName(), $title);
    }
}
