<?php

namespace SilverStripe\Core\Tests\Validation\FieldValidation;

use SilverStripe\Dev\SapphireTest;
use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Core\Validation\FieldValidation\NumericFieldValidator;
use InvalidArgumentException;

class NumericFieldValidatorTest extends SapphireTest
{
    public static function provideValidateType(): array
    {
        return [
            'valid-int' => [
                'value' => 123,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-zero' => [
                'value' => 0,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-negative-int' => [
                'value' => -123,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-float' => [
                'value' => 123.45,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-negative-float' => [
                'value' => -123.45,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-max-int' => [
                'value' => PHP_INT_MAX,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-min-int' => [
                'value' => PHP_INT_MIN,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-max-float' => [
                'value' => PHP_FLOAT_MAX,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-min-float' => [
                'value' => PHP_FLOAT_MIN,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-null' => [
                'value' => null,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-string' => [
                'value' => '123',
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-numeric-string' => [
                'value' => '123',
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'invalid-not-numeric-string' => [
                'value' => 'fish',
                'expectedIsValid' => false,
                'expectedMessage' => 'Must be numeric',
            ],
            'invalid-array' => [
                'value' => [123],
                'expectedIsValid' => false,
                'expectedMessage' => 'Must be numeric',
            ],
            'invalid-true' => [
                'value' => true,
                'expectedIsValid' => false,
                'expectedMessage' => 'Must be numeric',
            ],
            'invalid-false' => [
                'value' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Must be numeric',
            ],
        ];
    }

    #[DataProvider('provideValidateType')]
    public function testValidateType(
        mixed $value,
        bool $expectedIsValid,
        ?string $expectedMessage
    ): void {
        $validator = new NumericFieldValidator('MyField', $value);
        $result = $validator->validate();
        $this->assertSame($expectedIsValid, $result->isValid());
        if (!$result->isValid()) {
            $this->assertSame($expectedMessage, $result->getMessages()[0]['message']);
        }
    }

    public static function provideValidate(): array
    {
        return [
            'valid' => [
                'value' => 10,
                'minValue' => null,
                'maxValue' => null,
                'exception' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-min' => [
                'value' => 15,
                'minValue' => 10,
                'maxValue' => null,
                'exception' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-min-equal' => [
                'value' => 10,
                'minValue' => 10,
                'maxValue' => null,
                'exception' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-max' => [
                'value' => 5,
                'minValue' => null,
                'maxValue' => 10,
                'exception' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-max-equal' => [
                'value' => 10,
                'minValue' => null,
                'maxValue' => 10,
                'exception' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-min-max-between' => [
                'value' => 15,
                'minValue' => 10,
                'maxValue' => 20,
                'exception' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-min-max-equal' => [
                'value' => 10,
                'minValue' => 10,
                'maxValue' => 10,
                'exception' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'exception-min-above-max' => [
                'value' => 15,
                'minValue' => 20,
                'maxValue' => 10,
                'exception' => true,
                'expectedIsValid' => false,
                'expectedMessage' => '',
            ],
            'invalid-below-min' => [
                'value' => 5,
                'minValue' => 10,
                'maxValue' => 20,
                'exception' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Value cannot be less than 10',
            ],
            'invalid-above-max' => [
                'value' => 25,
                'minValue' => 10,
                'maxValue' => 20,
                'exception' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Value cannot be greater than 20',
            ],
        ];
    }

    #[DataProvider('provideValidate')]
    public function testValidate(
        int $value,
        ?int $minValue,
        ?int $maxValue,
        bool $exception,
        bool $expectedIsValid,
        ?string $expectedMessage,
    ): void {
        if ($exception) {
            $this->expectException(InvalidArgumentException::class);
        }
        $validator = new NumericFieldValidator('MyField', $value, $minValue, $maxValue);
        $result = $validator->validate();
        if ($exception) {
            return;
        }
        $this->assertSame($expectedIsValid, $result->isValid());
        if (!$result->isValid()) {
            $this->assertSame($expectedMessage, $result->getMessages()[0]['message']);
        }
    }
}
