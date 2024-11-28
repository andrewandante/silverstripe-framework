<?php

namespace SilverStripe\Forms\Tests;

use SilverStripe\Forms\SingleLookupField;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Class SingleLookupFieldTest
 *
 * @package SilverStripe\Forms\Tests
 */
class SingleLookupFieldTest extends SapphireTest
{
    public function testValueFromSource()
    {
        /** @var SingleLookupField $testField */
        $testField = DropdownField::create(
            'FirstName',
            'FirstName',
            ['member1' => 'Member 1', 'member2' => 'Member 2', 'member3' => 'Member 3']
        )->performReadonlyTransformation();

        $this->assertInstanceOf(SingleLookupField::class, $testField);

        $testField->setValue('member1');
        preg_match('/Member 1/', $testField->Field() ?? '', $matches);
        $this->assertEquals($matches[0], 'Member 1');
    }

    public function testValueNotFromSource()
    {
        /** @var SingleLookupField $testField */
        $testField = DropdownField::create(
            'FirstName',
            'FirstName',
            ['member1' => 'Member 1', 'member2' => 'Member 2', 'member3' => 'Member 3']
        )->performReadonlyTransformation();

        $this->assertInstanceOf(SingleLookupField::class, $testField);

        $testField->setValue('member123');
        preg_match('/\(none\)/', $testField->Field() ?? '', $matches);
        $this->assertEquals($matches[0], '(none)');
    }

    public static function provideValidate(): array
    {
        return [
            'valid-value' => [
                'value' => 1,
                'expected' => true,
            ],
            // test that validation isn't being applied to read-only field
            'valid-not-value' => [
                'value' => 3,
                'expected' => true,
            ],
        ];
    }

    #[DataProvider('provideValidate')]
    public function testValidate(mixed $value, bool $expected): void
    {
        $field = new SingleLookupField('Test', 'Test', [1 => 'cat', 2 => 'dog']);
        $field->setValue($value);
        $this->assertSame($expected, $field->validate()->isValid());
    }
}
