<?php

namespace SilverStripe\Forms\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\RequiredFields;
use PHPUnit\Framework\Attributes\DataProvider;

class TextareaFieldTest extends SapphireTest
{

    public function testCasting()
    {
        // Test special characters
        $inputText = "These are some unicodes: ä, ö, & ü";
        $field = new TextareaField("Test", "Test");
        $field->setValue($inputText);
        $this->assertStringContainsString('These are some unicodes: &auml;, &ouml;, &amp; &uuml;', $field->Field());
        // Test shortcodes
        $inputText = "Shortcode: [file_link id=4]";
        $field = new TextareaField("Test", "Test");
        $field->setValue($inputText);
        $this->assertStringContainsString('Shortcode: [file_link id=4]', $field->Field());
    }

    /**
     * Quick smoke test to ensure that text with unicodes is being displayed properly in readonly fields.
     */
    public function testReadonlyDisplayUnicodes()
    {
        $inputText = "These are some unicodes: äöü";
        $field = new TextareaField("Test", "Test");
        $field->setValue($inputText);
        $field = $field->performReadonlyTransformation();
        $this->assertStringContainsString('These are some unicodes: äöü', $field->Field());
    }

    /**
     * Quick smoke test to ensure that text with special html chars is being displayed properly in readonly fields.
     */
    public function testReadonlyDisplaySpecialHTML()
    {
        $inputText = "These are some special <html> chars including 'single' & \"double\" quotations";
        $field = new TextareaField("Test", "Test");
        $field = $field->performReadonlyTransformation();
        $field->setValue($inputText);
        $this->assertStringContainsString(
            'These are some special &lt;html&gt; chars including &#039;single&#039; &amp;'
            . ' &quot;double&quot; quotations',
            $field->Field()
        );
    }

    /**
     * Tests the TextareaField Max Length Validation Failure
     */
    public function testMaxLengthValidationFail()
    {
        $field = new TextareaField("Test", "Test");
        $field->setMaxLength(5);
        $field->setValue("John Doe"); // 8 characters, so should fail
        $this->assertFalse($field->validate()->isValid());
    }

    /**
     * Tests the TextareaField Max Length Validation Success
     */
    public function testMaxLengthValidationSuccess()
    {
        $field = new TextareaField("Test", "Test");
        $field->setMaxLength(5);
        $field->setValue("John"); // 4 characters, so should pass
        $this->assertTrue($field->validate()->isValid());
    }

    public function testValueEntities()
    {
        $inputText = "These <b>are</b> some unicodes: äöü";
        $field = new TextareaField("Test", "Test");
        $field->setValue($inputText);

        // Value should be safe-encoding only, but ValueEntities should be more aggressive
        $this->assertEquals(
            "These &lt;b&gt;are&lt;/b&gt; some unicodes: &auml;&ouml;&uuml;",
            $field->obj('ValueEntities')->forTemplate()
        );

        // Shortcodes are disabled
        $this->assertEquals(
            false,
            $field->obj('ValueEntities')->getProcessShortcodes()
        );
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
        $field = new TextareaField('Test');
        $field->setMaxLength(3);
        $field->setValue($value);
        $this->assertSame($expected, $field->validate()->isValid());
    }
}
