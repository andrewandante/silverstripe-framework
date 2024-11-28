<?php

namespace SilverStripe\Forms\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Forms\SearchableDropdownField;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\Tests\SearchableDropdownFieldTest\Biscuit;
use SilverStripe\Security\Group;

class SearchableDropdownFieldTest extends SapphireTest
{
    public static function provideGetValueForValidation(): array
    {
        return [
            'int-single-value' => [
                'value' => 333,
                'expected' => 333,
            ],
            'array-single-value' => [
                'value' => [333],
                'expected' => 333,
            ],
            'array-multi-value' => [
                'value' => [333, 444],
                'expected' => [333, 444],
            ],
            'null' => [
                'value' => null,
                'expected' => null,
            ],
        ];
    }

    #[DataProvider('provideGetValueForValidation')]
    public function testGetValueForValidation(mixed $value, mixed $expected): void
    {
        $field = new SearchableDropdownField('Test', 'Test', Group::get());
        $field->setValue($value);
        $actual = $field->getValueForValidation($value);
        $this->assertSame($expected, $actual);
    }
}
