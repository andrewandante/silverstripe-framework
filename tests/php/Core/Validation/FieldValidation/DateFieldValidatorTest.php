<?php

namespace SilverStripe\Core\Tests\Validation\FieldValidation;

use SilverStripe\Dev\SapphireTest;
use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Core\Validation\FieldValidation\DateFieldValidator;
use InvalidArgumentException;

class DateFieldValidatorTest extends SapphireTest
{
    public static function provideValidate(): array
    {
        return [
            'valid' => [
                'value' => '2024-09-15',
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-in-range' => [
                'value' => '2024-09-15',
                'minValue' => '2024-08-15',
                'maxValue' => '2024-10-15',
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'valid-null' => [
                'value' => null,
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'exception-max-lt-min' => [
                'value' => '2024-09-15',
                'minValue' => '2024-08-15',
                'maxValue' => '2024-07-15',
                'converter' => false,
                'expection' => true,
                'expectedIsValid' => false,
                'expectedMessage' => null,
            ],
            'invalid' => [
                'value' => '2024-02-30',
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Invalid date',
            ],
            'invalid-blank-string' => [
                'value' => '',
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Invalid date',
            ],
            'invalid-too-low' => [
                'value' => '2024-07-15',
                'minValue' => '2024-08-15',
                'maxValue' => '2024-10-15',
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Value cannot be older than 2024-08-15',
            ],
            'invalid-too-low-converter' => [
                'value' => '2024-07-15',
                'minValue' => '2024-08-15',
                'maxValue' => '2024-10-15',
                'converter' => true,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Value cannot be older than ***2024-08-15***',
            ],
            'invalid-too-high' => [
                'value' => '2024-11-15',
                'minValue' => '2024-08-15',
                'maxValue' => '2024-10-15',
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Value cannot be newer than 2024-10-15',
            ],
            'invalid-too-high-converter' => [
                'value' => '2024-11-15',
                'minValue' => '2024-08-15',
                'maxValue' => '2024-10-15',
                'converter' => true,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Value cannot be newer than ***2024-10-15***',
            ],
            'invalid-wrong-format' => [
                'value' => '15-09-2024',
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Invalid date',
            ],
            'invalid-date-time' => [
                'value' => '2024-09-15 13:34:56',
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Invalid date',
            ],
            'invalid-time' => [
                'value' => '13:34:56',
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Invalid date',
            ],
            'invalid-not-string' => [
                'value' => ['2024-09-15'],
                'minValue' => null,
                'maxValue' => null,
                'converter' => false,
                'expection' => false,
                'expectedIsValid' => false,
                'expectedMessage' => 'Must be a string',
            ],
        ];
    }

    #[DataProvider('provideValidate')]
    public function testValidate(
        mixed $value,
        ?string $minValue,
        ?string $maxValue,
        bool $converter,
        bool $expection,
        bool $expectedIsValid,
        ?string $expectedMessage
    ): void {
        if ($expection) {
            $this->expectException(InvalidArgumentException::class);
        }
        $callable = $converter ? function ($value) {
            return "***$value***";
        } : null;
        $validator = new DateFieldValidator('MyField', $value, $minValue, $maxValue, $callable);
        $result = $validator->validate();
        if ($expection) {
            return;
        }
        $this->assertSame($expectedIsValid, $result->isValid());
        if (!$result->isValid()) {
            $this->assertSame($expectedMessage, $result->getMessages()[0]['message']);
        }
    }
}
