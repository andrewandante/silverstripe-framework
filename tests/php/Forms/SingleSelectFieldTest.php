<?php

namespace SilverStripe\Forms\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\SingleSelectField;

class SingleSelectFieldTest extends SapphireTest
{
    public static function provideGetValueForValidation(): array
    {
        return [
            'cat-not-empty-default' => [
                'value' => 'cat',
                'hasEmptyDefault' => false,
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => 'cat',
            ],
            'cat-empty-default' => [
                'value' => 'cat',
                'hasEmptyDefault' => true,
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => 'cat',
            ],
            'blank-not-empty-default' => [
                'value' => '',
                'hasEmptyDefault' => false,
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => '',
            ],
            'blank-empty-default' => [
                'value' => '',
                'hasEmptyDefault' => true,
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => null,
            ],
            'null-not-empty-default' => [
                'value' => null,
                'hasEmptyDefault' => false,
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => null,
            ],
            'null-empty-default' => [
                'value' => null,
                'hasEmptyDefault' => true,
                'source' => ['cat' => 'cat', 'dog' => 'dog'],
                'expected' => null,
            ],
            'int-source-int-value' => [
                'value' => 3,
                'hasEmptyDefault' => false,
                'source' => [3 => 'cat', 4 => 'dog'],
                'expected' => 3,
            ],
            'int-source-string-value' => [
                'value' => '3',
                'hasEmptyDefault' => false,
                'source' => [3 => 'cat', 4 => 'dog'],
                'expected' => 3,
            ],
            // not testing string-int source i.e. ['3' => 'cat', '4' => 'dog'], as PHP will convert the keys to int
        ];
    }

    #[DataProvider('provideGetValueForValidation')]
    public function testGetValueForValidation(
        mixed $value,
        bool $hasEmptyDefault,
        array $source,
        mixed $expected,
    ): void {
        $field = new class ('Test') extends SingleSelectField {
        };
        $field->setSource($source);
        $field->setHasEmptyDefault($hasEmptyDefault);
        $field->setValue($value);
        $this->assertSame($expected, $field->getValueForValidation($value));
    }
}
