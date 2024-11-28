<?php

namespace SilverStripe\Core\Tests\Validation\FieldValidation;

use SilverStripe\Dev\SapphireTest;
use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Core\Validation\FieldValidation\NumericNonStringFieldValidator;

class NumericNonStringFieldValidatorTest extends SapphireTest
{
    public static function provideValidateType(): array
    {
        return [
            'valid-int' => [
                'value' => 123,
                'expectedIsValid' => true,
                'expectedMessage' => null,
            ],
            'invalid-int-string' => [
                'value' => '123',
                'expectedIsValid' => false,
                'expectedMessage' => 'Must be numeric and not a string',
            ],
        ];
    }

    #[DataProvider('provideValidateType')]
    public function testValidateType(
        mixed $value,
        bool $expectedIsValid,
        ?string $expectedMessage
    ): void {
        $validator = new NumericNonStringFieldValidator('MyField', $value);
        $result = $validator->validate();
        $this->assertSame($expectedIsValid, $result->isValid());
        if (!$result->isValid()) {
            $this->assertSame($expectedMessage, $result->getMessages()[0]['message']);
        }
    }
}
