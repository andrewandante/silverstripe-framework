<?php

namespace SilverStripe\Forms;

use IntlDateFormatter;
use SilverStripe\i18n\i18n;
use InvalidArgumentException;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Core\Validation\FieldValidation\DateFieldValidator;
use SilverStripe\Forms\FieldValidatorConverter\FieldValidatorConverterInterface;
use SilverStripe\Forms\FieldValidatorConverter\FieldValidatorConverterTrait;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Form used for editing a date string
 *
 * Caution: The form field does not include any JavaScript or CSS when used outside of the CMS context,
 * since the required frontend dependencies are included through CMS bundling.
 *
 * # Localization
 *
 * Date formatting can be controlled in the below order of priority:
 *  - Format set via setDateFormat()
 *  - Format generated from current locale set by setLocale() and setDateLength()
 *  - Format generated from current locale in i18n
 *
 * You can also specify a setClientLocale() to set the javascript to a specific locale
 * on the frontend. However, this will not override the date format string.
 *
 * See http://doc.silverstripe.org/framework/en/topics/i18n for more information about localizing form fields.
 *
 * # Usage
 *
 * ## Example: Field localised with german date format
 *
 *   $f = new DateField('MyDate');
 *   $f->setLocale('de_DE');
 *
 * # Validation
 *
 * Caution: JavaScript validation is only supported for the 'en_NZ' locale at the moment,
 * it will be disabled automatically for all other locales.
 *
 * # Formats
 *
 * All format strings should follow the CLDR standard as per
 * https://unicode-org.github.io/icu/userguide/format_parse/datetime These will be converted
 * automatically to jquery UI format.
 *
 * The value of this field in PHP will be ISO 8601 standard (e.g. 2004-02-12), and
 * stores this as a timestamp internally.
 *
 * Note: Do NOT use php date format strings. Date format strings follow the date
 * field symbol table as below.
 *
 * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime
 * @see http://api.jqueryui.com/datepicker/#utility-formatDate
 */
class DateField extends TextField implements FieldValidatorConverterInterface
{
    use FieldValidatorConverterTrait;

    private static array $field_validators = [
        DateFieldValidator::class => [
            'minValue' => 'getMinDate',
            'maxValue' => 'getMaxDate',
            'converter' => 'getFieldValidatorConverter',
        ],
    ];
    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_DATE;

    /**
     * Override locale. If empty will default to current locale
     *
     * @var string
     */
    protected $locale = null;

    /**
     * Override date format. If empty will default to that used by the current locale.
     *
     * @var null
     */
    protected $dateFormat = null;

    /**
     * Length of this date (full, short, etc).
     *
     * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @var int
     */
    protected $dateLength = null;

    protected $inputType = 'date';

    /**
     * Min date
     *
     * @var string ISO 8601 date for min date
     */
    protected $minDate = null;

    /**
     * Max date
     *
     * @var string ISO 860 date for max date
     */
    protected $maxDate = null;

    /**
     * Use HTML5-based input fields (and force ISO 8601 date formats).
     *
     * @var bool
     */
    protected $html5 = true;

    /**
     * @return bool
     */
    public function getHTML5()
    {
        return $this->html5;
    }

    /**
     * @param boolean $bool
     * @return $this
     */
    public function setHTML5($bool)
    {
        $this->html5 = $bool;
        return $this;
    }

    /**
     * Get length of the date format to use. One of:
     *
     *  - IntlDateFormatter::SHORT
     *  - IntlDateFormatter::MEDIUM
     *  - IntlDateFormatter::LONG
     *  - IntlDateFormatter::FULL
     *
     * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @return int
     */
    public function getDateLength()
    {
        if ($this->dateLength) {
            return $this->dateLength;
        }
        return IntlDateFormatter::MEDIUM;
    }

    /**
     * Get length of the date format to use.
     * Only applicable with {@link setHTML5(false)}.
     *
     * @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     *
     * @param int $length
     * @return $this
     */
    public function setDateLength($length)
    {
        $this->dateLength = $length;
        return $this;
    }

    /**
     * Get date format in CLDR standard format
     *
     * This can be set explicitly. If not, this will be generated from the current locale
     * with the current date length.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
     */
    public function getDateFormat()
    {
        // Browsers expect ISO 8601 dates, localisation is handled on the client
        if ($this->getHTML5()) {
            return DBDate::ISO_DATE;
        }

        if ($this->dateFormat) {
            return $this->dateFormat;
        }

        // Get from locale
        return $this->getFrontendFormatter()->getPattern();
    }

    /**
     * Set date format in CLDR standard format.
     * Only applicable with {@link setHTML5(false)}.
     *
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table
     * @param string $format
     * @return $this
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
        return $this;
    }

    /**
     * Get date formatter with the standard locale / date format
     *
     * @throws \LogicException
     * @return IntlDateFormatter
     */
    protected function getFrontendFormatter()
    {
        if ($this->getHTML5() && $this->dateFormat && $this->dateFormat !== DBDate::ISO_DATE) {
            throw new \LogicException(
                'Please opt-out of HTML5 processing of ISO 8601 dates via setHTML5(false) if using setDateFormat()'
            );
        }

        if ($this->getHTML5() && $this->dateLength) {
            throw new \LogicException(
                'Please opt-out of HTML5 processing of ISO 8601 dates via setHTML5(false) if using setDateLength()'
            );
        }

        if ($this->getHTML5() && $this->locale && $this->locale !== DBDate::ISO_LOCALE) {
            throw new \LogicException(
                'Please opt-out of HTML5 processing of ISO 8601 dates via setHTML5(false) if using setLocale()'
            );
        }

        $formatter = IntlDateFormatter::create(
            $this->getLocale(),
            $this->getDateLength(),
            IntlDateFormatter::NONE
        );

        if ($this->getHTML5()) {
            // Browsers expect ISO 8601 dates, localisation is handled on the client
            $formatter->setPattern(DBDate::ISO_DATE);
        } elseif ($this->dateFormat) {
            // Don't invoke getDateFormat() directly to avoid infinite loop
            $ok = $formatter->setPattern($this->dateFormat);
            if (!$ok) {
                throw new InvalidArgumentException("Invalid date format {$this->dateFormat}");
            }
        }
        return $formatter;
    }

    /**
     * Get a date formatter for the ISO 8601 format
     *
     * @return IntlDateFormatter
     */
    protected function getInternalFormatter()
    {
        $formatter = IntlDateFormatter::create(
            DBDate::ISO_LOCALE,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE
        );
        $formatter->setLenient(false);
        // CLDR ISO 8601 date.
        $formatter->setPattern(DBDate::ISO_DATE);
        return $formatter;
    }

    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        $attributes['lang'] = i18n::convert_rfc1766($this->getLocale());

        if ($this->getHTML5()) {
            $attributes['min'] = $this->getMinDate();
            $attributes['max'] = $this->getMaxDate();
        } else {
            $attributes['type'] = 'text';
        }

        return $attributes;
    }

    public function getSchemaDataDefaults()
    {
        $defaults = parent::getSchemaDataDefaults();
        return array_merge($defaults, [
            'lang' => i18n::convert_rfc1766($this->getLocale()),
            'data' => array_merge($defaults['data'], [
                'html5' => $this->getHTML5(),
                'min' => $this->getMinDate(),
                'max' => $this->getMaxDate()
            ])
        ]);
    }

    public function Type()
    {
        return 'date text';
    }

    /**
     * Assign value posted from form submission
     *
     * @param mixed $value
     * @param mixed $data
     * @return $this
     */
    public function setSubmittedValue($value, $data = null)
    {
        // Null case
        if (!$value) {
            $this->value = null;
            return $this;
        }

        // Parse from submitted value
        $this->value = $this->frontendToInternal($value);
        return $this;
    }

    /**
     * Assign value based on {@link $datetimeFormat}, which might be localised.
     *
     * When $html5=true, assign value from ISO 8601 string.
     *
     * @param mixed $value
     * @param mixed $data
     * @return $this
     */
    public function setValue($value, $data = null)
    {
        if (is_null($value)) {
            $this->value = null;
            return $this;
        }

        // Re-run through formatter to tidy up (e.g. remove time component)
        $this->value = $this->tidyInternal($value, false);
        return $this;
    }

    public function Value()
    {
        return $this->internalToFrontend($this->value);
    }

    public function getValueForValidation(): mixed
    {
        // new DateField('MyDate') ... getValue() will return empty string
        // return null so that validation is skipped
        if ($this->getValue() === '') {
            return null;
        }
        return $this->value;
    }

    public function performReadonlyTransformation()
    {
        $field = $this
            ->castedCopy(DateField_Disabled::class)
            ->setValue($this->dataValue())
            ->setLocale($this->getLocale())
            ->setReadonly(true);

        return $field;
    }

    /**
     * Get locale to use for this field
     *
     * @return string
     */
    public function getLocale()
    {
        // Use iso locale for html5
        if ($this->getHTML5()) {
            return DBDate::ISO_LOCALE;
        }
        return $this->locale ?: i18n::get_locale();
    }

    /**
     * Determines the presented/processed format based on locale defaults,
     * instead of explicitly setting {@link setDateFormat()}.
     * Only applicable with {@link setHTML5(false)}.
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function getSchemaValidation()
    {
        $rules = parent::getSchemaValidation();
        $rules['date'] = true;
        return $rules;
    }

    /**
     * @return string
     */
    public function getMinDate()
    {
        return $this->minDate;
    }

    /**
     * @param string $minDate
     * @return $this
     */
    public function setMinDate($minDate)
    {
        $this->minDate = $this->tidyInternal($minDate, true);
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxDate()
    {
        return $this->maxDate;
    }

    /**
     * @param string $maxDate
     * @return $this
     */
    public function setMaxDate($maxDate)
    {
        $this->maxDate = $this->tidyInternal($maxDate, true);
        return $this;
    }

    /**
     * Convert frontend date to the internal representation (ISO 8601).
     * The frontend date is also in ISO 8601 when $html5=true.
     *
     * @param string $date
     * @return string The formatted date, or null if not a valid date
     */
    protected function frontendToInternal($date)
    {
        if (!$date) {
            return null;
        }
        $fromFormatter = $this->getFrontendFormatter();
        $toFormatter = $this->getInternalFormatter();
        $timestamp = $fromFormatter->parse($date);
        if ($timestamp === false) {
            return null;
        }
        return $toFormatter->format($timestamp) ?: null;
    }

    /**
     * Convert the internal date representation (ISO 8601) to a format used by the frontend,
     * as defined by {@link $dateFormat}. With $html5=true, the frontend date will also be
     * in ISO 8601.
     *
     * @return string The formatted date, or null if not a valid date
     */
    public function internalToFrontend(mixed $value): ?string
    {
        $date = $this->tidyInternal($value, true);
        if (is_null($date)) {
            return null;
        }
        $fromFormatter = $this->getInternalFormatter();
        $toFormatter = $this->getFrontendFormatter();
        $timestamp = $fromFormatter->parse($date);
        if ($timestamp === false) {
            return null;
        }
        return $toFormatter->format($timestamp) ?: null;
    }

    /**
     * Tidy up the internal date representation (ISO 8601),
     * and fall back to strtotime() if there's parsing errors.
     *
     * @param mixed $date Date in ISO 8601 or approximate form
     * @param bool $returnNullOnFailure return null if the date is not valid, or the original date
     * @return null|string ISO 8601 date or null
     */
    protected function tidyInternal(mixed $date, bool $returnNullOnFailure): ?string
    {
        if (is_null($date)) {
            return null;
        }
        // Re-run through formatter to tidy up (e.g. remove time component)
        $formatter = $this->getInternalFormatter();
        $timestamp = $formatter->parse($date);
        if ($timestamp === false) {
            // Fallback to strtotime
            $timestamp = strtotime($date ?? '', DBDatetime::now()->getTimestamp());
            if ($timestamp === false) {
                return $returnNullOnFailure ? null : $date;
            }
        }
        return $formatter->format($timestamp);
    }
}
