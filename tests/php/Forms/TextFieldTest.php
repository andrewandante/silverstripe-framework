<?php

namespace SilverStripe\Forms\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\Forms\Tip;
use PHPUnit\Framework\Attributes\DataProvider;

class TextFieldTest extends SapphireTest
{

    /**
     * Tests the TextField Max Length Validation Failure
     */
    public function testMaxLengthValidationFail()
    {
        $textField = new TextField('TestField');
        $textField->setMaxLength(5);
        $textField->setValue("John Doe"); // 8 characters, so should fail
        $this->assertFalse($textField->validate()->isValid());
    }

    /**
     * Tests the TextField Max Length Validation Success
     */
    public function testMaxLengthValidationSuccess()
    {
        $textField = new TextField('TestField');
        $textField->setMaxLength(5);
        $textField->setValue("John"); // 4 characters, so should pass
        $this->assertTrue($textField->validate()->isValid());
    }

    /**
     * Ensures that when a Tip is applied to the field, it outputs it in the schema
     */
    public function testTipIsIncludedInSchema()
    {
        $textField = new TextField('TestField');
        $this->assertArrayNotHasKey('tip', $textField->getSchemaDataDefaults());

        $textField->setTip(new Tip('TestTip'));
        $this->assertArrayHasKey('tip', $textField->getSchemaDataDefaults());
    }

    public static function provideValidate(): array
    {
        return [
            'valid-string' => [
                'value' => 'abc',
                'expected' => true,
            ],
            'valid-blank-string' => [
                'value' => '',
                'expected' => true,
            ],
            'valid-null' => [
                'value' => null,
                'expected' => true,
            ],
            'invalid-too-long' => [
                'value' => 'abcd',
                'expected' => false,
            ],
            'invalid-int' => [
                'value' => 123,
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('provideValidate')]
    public function testValidate(mixed $value, bool $expected): void
    {
        $field = new TextField('Test');
        $field->setMaxLength(3);
        $field->setValue($value);
        $this->assertSame($expected, $field->validate()->isValid());
    }
}
