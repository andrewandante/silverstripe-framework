<?php

namespace SilverStripe\Core\Tests\Validation\FieldValidation;

use InvalidArgumentException;
use SilverStripe\Dev\SapphireTest;
use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Core\Validation\FieldValidation\MultiOptionFieldValidator;

class MultiOptionFieldValidatorTest extends SapphireTest
{
    public static function provideValidate(): array
    {
        return [
            'valid-string' => [
                'value' => ['cat'],
                'allowedValues' => ['cat', 'dog'],
                'expected' => true,
            ],
            'valid-multi-string' => [
                'value' => ['cat', 'dog'],
                'allowedValues' => ['cat', 'dog'],
                'expected' => true,
            ],
            'valid-none' => [
                'value' => [],
                'allowedValues' => ['cat', 'dog'],
                'expected' => true,
            ],
            'valid-int' => [
                'value' => [123],
                'allowedValues' => [123, 456],
                'expected' => true,
            ],
            'exception-not-array' => [
                'value' => 'cat',
                'allowedValues' => ['cat', 'dog'],
                'expected' => false,
            ],
            'exception-not-array-comma' => [
                'value' => 'cat,dog',
                'allowedValues' => ['cat', 'dog'],
                'expected' => false,
            ],
            'invalid' => [
                'value' => ['fish'],
                'allowedValues' => ['cat', 'dog'],
                'expected' => false,
            ],
            'invalid-null' => [
                'value' => [null],
                'allowedValues' => ['cat', 'dog'],
                'expected' => false,
            ],
            'invalid-multi' => [
                'value' => ['dog', 'fish'],
                'allowedValues' => ['cat', 'dog'],
                'expected' => false,
            ],
            'invalid-strict' => [
                'value' => ['123'],
                'allowedValues' => [123, 456],
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('provideValidate')]
    public function testValidate(mixed $value, array $allowedValues, bool $expected): void
    {
        $validator = new MultiOptionFieldValidator('MyField', $value, $allowedValues);
        $result = $validator->validate();
        $this->assertSame($expected, $result->isValid());
    }
}
