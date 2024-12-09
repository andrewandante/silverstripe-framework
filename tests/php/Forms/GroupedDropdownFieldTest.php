<?php

namespace SilverStripe\Forms\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GroupedDropdownField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;

class GroupedDropdownFieldTest extends SapphireTest
{

    public function testValidation()
    {
        $field = GroupedDropdownField::create(
            'Test',
            'Testing',
            [
                "1" => "One",
                "Group One" => [
                    "2" => "Two",
                    "3" => "Three"
                ],
                "Group Two" => [
                    "4" => "Four"
                ],
            ]
        );
        // Despite setting string keys "1", "2", "3", "4", PHP (not Silverstripe)
        // will convert these to int 1, 2, 3, 4
        $this->assertSame([1, 2, 3, 4], $field->getValidValues());

        $field->setValue(1);
        $this->assertTrue($field->validate()->isValid());

        //test grouped values
        $field->setValue(3);
        $this->assertTrue($field->validate()->isValid());

        //non-existent value should make the field invalid
        $field->setValue("Over 9000");
        $this->assertFalse($field->validate()->isValid());

        //empty string shouldn't validate
        $field->setValue('');
        $this->assertFalse($field->validate()->isValid());

        //empty field should validate after being set
        $field->setEmptyString('Empty String');
        $field->setValue('');
        $this->assertTrue($field->validate()->isValid());

        //disabled items shouldn't validate
        $field->setDisabledItems([1]);
        $field->setValue(1);

        $this->assertSame([2, 3, 4], $field->getValidValues());
        $this->assertSame([1], $field->getDisabledItems());

        $this->assertFalse($field->validate()->isValid());
    }

    /**
     * Test that empty-string values are supported by GroupDropdownTest
     */
    public function testEmptyString()
    {
        // Case A: empty value in the top level of the source
        $field = GroupedDropdownField::create(
            'Test',
            'Testing',
            [
                "" => "(Choose A)",
                "1" => "One",
                "Group One" => [
                    "2" => "Two",
                    "3" => "Three"
                ],
                "Group Two" => [
                    "4" => "Four"
                ],
            ]
        );

        $this->assertMatchesRegularExpression(
            '/<option value="" selected="selected" >\(Choose A\)<\/option>/',
            preg_replace('/\s+/', ' ', (string)$field->Field())
        );

        // Case B: empty value in the nested level of the source
        $field = GroupedDropdownField::create(
            'Test',
            'Testing',
            [
                "1" => "One",
                "Group One" => [
                    "" => "(Choose B)",
                    "2" => "Two",
                    "3" => "Three"
                ],
                "Group Two" => [
                    "4" => "Four"
                ],
            ]
        );
        $this->assertMatchesRegularExpression(
            '/<option value="" selected="selected" >\(Choose B\)<\/option>/',
            preg_replace('/\s+/', ' ', (string)$field->Field())
        );

        // Case C: setEmptyString
        $field = GroupedDropdownField::create(
            'Test',
            'Testing',
            [
                "1" => "One",
                "Group One" => [
                    "2" => "Two",
                    "3" => "Three"
                ],
                "Group Two" => [
                    "4" => "Four"
                ],
            ]
        );
        $field->setEmptyString('(Choose C)');
        $this->assertMatchesRegularExpression(
            '/<option value="" selected="selected" >\(Choose C\)<\/option>/',
            preg_replace('/\s+/', ' ', (string)$field->Field())
        );
    }

    /**
     * Test that readonly version of GroupedDropdownField displays all values
     */
    public function testReadonlyValue()
    {
        $field = GroupedDropdownField::create(
            'Test',
            'Testing',
            [
                "1" => "One",
                "Group One" => [
                    "2" => "Two",
                    "3" => "Three"
                ],
                "Group Two" => [
                    "4" => "Four"
                ],
            ]
        );

        // value on first level
        $field->setValue("1");
        $this->assertMatchesRegularExpression(
            '#<span class="readonly" id="Test">One</span>\n?<input type="hidden" name="Test" value="1" />#',
            (string)$field->performReadonlyTransformation()->Field()
        );

        // value on first level
        $field->setValue("2");
        $this->assertMatchesRegularExpression(
            '#<span class="readonly" id="Test">Two</span>\n?<input type="hidden" name="Test" value="2" />#',
            (string)$field->performReadonlyTransformation()->Field()
        );
    }
}
