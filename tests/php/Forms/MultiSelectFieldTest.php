<?php

namespace SilverStripe\Forms\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\MultiSelectField;

class MultiSelectFieldTest extends SapphireTest
{
    public static function provideSetSubmittedValue(): array
    {
        return [
            'string-index' => [
                'value' => ['cat'],
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => ['cat'],
            ],
            'int-index' => [
                'value' => [1],
                'source' => [1 => 'cat', 2 => 'dog'],
                'expected' => [1],
            ],
            'int-index-string-value' => [
                'value' => ['1'],
                'source' => [1 => 'cat', 2 => 'dog'],
                'expected' => ['1'],
            ],
            'string-int-index-string-value' => [
                'value' => ['1'],
                // PHP (not Silverstripe) will convert the "1" string to an integer 1 when array_keys()
                // is called in SelectField::getSourceValues()
                'source' => ['1' => 'cat', '2' => 'dog'],
                'expected' => ['1'],
            ],
            'string-int-index-int-value' => [
                'value' => [1],
                'source' => ["1" => 'cat', "2" => 'dog'],
                'expected' => [1],
            ],
            'empty-value' => [
                'value' => [""],
                'source' => [1 => 'cat', 2 => 'dog'],
                'expected' => null,
            ],
            'null' => [
                'value' => null,
                'source' => [1 => 'cat', 2 => 'dog'],
                'expected' => null,
            ],
            'empty-array' => [
                'value' => [],
                'source' => [1 => 'cat', 2 => 'dog'],
                'expected' => [],
            ],
            'wrong' => [
                'value' => 999,
                'source' => [1 => 'cat', 2 => 'dog'],
                'expected' => 999,
            ],
        ];
    }

    #[DataProvider('provideSetSubmittedValue')]
    public function testSetSubmittedValue(mixed $value, array $source, mixed $expected): void
    {
        $field = new class ('MyField', 'MyField', $source) extends MultiSelectField {
        };
        $field->setSubmittedValue($value);
        $this->assertSame($expected, $field->getValue());
    }

    public static function provideGetValueForValidation(): array
    {
        return [
            'string-source-single-value' => [
                'value' => ['cat'],
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => ['cat'],
            ],
            'string-source-multi-value' => [
                'value' => ['cat', 'dog'],
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => ['cat', 'dog'],
            ],
            'int-source-single-value' => [
                'value' => [3],
                'source' => [3 => 'cat', 4 => 'dog'],
                'expected' => [3],
            ],
            'int-source-single-multi-value' => [
                'value' => [3, 4],
                'source' => [3 => 'cat', 4 => 'dog'],
                'expected' => [3, 4],
            ],
            'int-source-single-string-value' => [
                'value' => ['3'],
                'source' => [3 => 'cat', 4 => 'dog'],
                'expected' => [3],
            ],
            'int-source-mutli-string-value' => [
                'value' => ['3', '4'],
                'source' => [3 => 'cat', 4 => 'dog'],
                'expected' => [3, 4],
            ],
            // not testing string-int source i.e. ['3' => 'cat', '4' => 'dog'], as PHP will convert the keys to int
        ];
    }

    #[DataProvider('provideGetValueForValidation')]
    public function testGetValueForValidation(
        mixed $value,
        array $source,
        mixed $expected,
    ): void {
        $field = new class ('Test') extends MultiSelectField {
        };
        $field->setSource($source);
        $field->setValue($value);
        $this->assertSame($expected, $field->getValueForValidation($value));
    }
}
