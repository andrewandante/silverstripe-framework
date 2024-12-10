<?php

namespace SilverStripe\Forms\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\SearchableDropdownField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Security\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;

class RequiredFieldsValidatorTest extends SapphireTest
{
    public function testConstructingWithArray()
    {
        //can we construct with an array?
        $fields = [
            'Title',
            'Content',
            'Image',
            'AnotherField'
        ];
        $requiredFields = new RequiredFieldsValidator($fields);
        //check the fields and the array match
        $this->assertEquals(
            $fields,
            $requiredFields->getRequired(),
            "Failed to set the required fields using an array"
        );
    }

    public function testConstructingWithArguments()
    {
        //can we construct with arguments?
        $requiredFields = new RequiredFieldsValidator(
            'Title',
            'Content',
            'Image',
            'AnotherField'
        );
        //check the fields match
        $this->assertEquals(
            [
                'Title',
                'Content',
                'Image',
                'AnotherField'
            ],
            $requiredFields->getRequired(),
            "Failed to set the required fields using arguments"
        );
    }

    public function testRemoveValidation()
    {
        //can we remove all fields at once?
        $requiredFields = new RequiredFieldsValidator(
            'Title',
            'Content',
            'Image',
            'AnotherField'
        );
        $requiredFields->removeValidation();
        //check there are no required fields
        $this->assertEmpty(
            $requiredFields->getRequired(),
            "Failed to remove all the required fields using 'removeValidation()'"
        );
    }

    public function testRemoveRequiredField()
    {
        //set up the required fields
        $requiredFields = new RequiredFieldsValidator(
            'Title',
            'Content',
            'Image',
            'AnotherField'
        );
        //remove one
        $requiredFields->removeRequiredField('Content');
        //compare the arrays
        $this->assertEquals(
            [
                'Title',
                'Image',
                'AnotherField'
            ],
            $requiredFields->getRequired(),
            "Failed to remove the 'Content' field from required list"
        );
        //let's remove another
        $requiredFields->removeRequiredField('Title');
        $this->assertEquals(
            [
                'Image',
                'AnotherField'
            ],
            $requiredFields->getRequired(),
            "Failed to remove 'Title' field from required list"
        );
        //lets try to remove one that doesn't exist
        $requiredFields->removeRequiredField('DontExists');
        $this->assertEquals(
            [
                'Image',
                'AnotherField'
            ],
            $requiredFields->getRequired(),
            "Removing a non-existent field from required list altered the list of required fields"
        );
    }

    public function testAddRequiredField()
    {
        //set up the validator
        $requiredFields = new RequiredFieldsValidator(
            'Title'
        );
        //add a field
        $requiredFields->addRequiredField('Content');
        //check it was added
        $this->assertEquals(
            [
                'Title',
                'Content'
            ],
            $requiredFields->getRequired(),
            "Failed to add a new field to the required list"
        );
        //add another for good measure
        $requiredFields->addRequiredField('Image');
        //check it was added
        $this->assertEquals(
            [
                'Title',
                'Content',
                'Image'
            ],
            $requiredFields->getRequired(),
            "Failed to add a second new field to the required list"
        );
        //remove a field
        $requiredFields->removeRequiredField('Title');
        //check it was removed
        $this->assertEquals(
            [
                'Content',
                'Image'
            ],
            $requiredFields->getRequired(),
            "Failed to remove 'Title' field from required list"
        );
        //add the same field back to check we can add and remove at will
        $requiredFields->addRequiredField('Title');
        //check it's in there
        $this->assertEquals(
            [
                'Content',
                'Image',
                'Title'
            ],
            $requiredFields->getRequired(),
            "Failed to add 'Title' back to the required field list"
        );
        //add a field that already exists (we can't have the same field twice, can we?)
        $requiredFields->addRequiredField('Content');
        //check the field wasn't added
        $this->assertEquals(
            [
                'Content',
                'Image',
                'Title'
            ],
            $requiredFields->getRequired(),
            "Adding a duplicate field to required field list had unexpected behaviour"
        );
    }

    public function testAppendRequiredFields()
    {
        //get the validator
        $requiredFields = new RequiredFieldsValidator(
            'Title',
            'Content',
            'Image',
            'AnotherField'
        );
        //create another validator with other fields
        $otherRequiredFields = new RequiredFieldsValidator(
            [
            'ExtraField1',
            'ExtraField2'
            ]
        );
        //append the new fields
        $requiredFields->appendRequiredFields($otherRequiredFields);
        //check they were added correctly
        $this->assertEquals(
            [
                'Title',
                'Content',
                'Image',
                'AnotherField',
                'ExtraField1',
                'ExtraField2'
            ],
            $requiredFields->getRequired(),
            "Merging of required fields failed to behave as expected"
        );
        // create the standard validator so we can check duplicates are ignored
        $otherRequiredFields = new RequiredFieldsValidator(
            'Title',
            'Content',
            'Image',
            'AnotherField'
        );
        //add the new validator
        $requiredFields->appendRequiredFields($otherRequiredFields);
        //check nothing was changed
        $this->assertEquals(
            [
                'Title',
                'Content',
                'Image',
                'AnotherField',
                'ExtraField1',
                'ExtraField2'
            ],
            $requiredFields->getRequired(),
            "Merging of required fields with duplicates failed to behave as expected"
        );
        //add some new fields and some old ones in a strange order
        $otherRequiredFields = new RequiredFieldsValidator(
            'ExtraField3',
            'Title',
            'ExtraField4',
            'Image',
            'Content'
        );
        //add the new validator
        $requiredFields->appendRequiredFields($otherRequiredFields);
        //check that only the new fields were added
        $this->assertEquals(
            [
                'Title',
                'Content',
                'Image',
                'AnotherField',
                'ExtraField1',
                'ExtraField2',
                'ExtraField3',
                'ExtraField4'
            ],
            $requiredFields->getRequired(),
            "Merging of required fields with some duplicates in a muddled order failed to behave as expected"
        );
    }

    public function testFieldIsRequired()
    {
        //get the validator
        $requiredFields = new RequiredFieldsValidator(
            $fieldNames = [
            'Title',
            'Content',
            'Image',
            'AnotherField'
            ]
        );

        foreach ($fieldNames as $field) {
            $this->assertTrue(
                $requiredFields->fieldIsRequired($field),
                sprintf("Failed to find '%s' field in required list", $field)
            );
        }

        //add a new field
        $requiredFields->addRequiredField('ExtraField1');
        //check the new field is required
        $this->assertTrue(
            $requiredFields->fieldIsRequired('ExtraField1'),
            "Failed to find 'ExtraField1' field in required list after adding it to the list"
        );
        //check a non-existent field returns false
        $this->assertFalse(
            $requiredFields->fieldIsRequired('DoesntExist'),
            "Unexpectedly returned true for a non-existent field"
        );
    }

    public static function provideHasOneRelationFieldInterfaceValidation(): array
    {
        return [
            [
                'className' => TreeDropdownField::class,
            ],
            [
                'className' => SearchableDropdownField::class,
            ]
        ];
    }

    #[DataProvider('provideHasOneRelationFieldInterfaceValidation')]
    public function testHasOneRelationFieldInterfaceValidation(string $className)
    {
        $form = new Form();
        $param = $className === TreeDropdownField::class ? Group::class : Group::get();
        $field = new $className('TestField', 'TestField', $param);
        $form->Fields()->push($field);
        $validator = new RequiredFieldsValidator('TestField');
        $validator->setForm($form);
        // blank string and 0 and '0' and array with value of 0 fail required field validation
        $this->assertFalse($validator->php(['TestField' => '']));
        $this->assertFalse($validator->php(['TestField' => 0]));
        $this->assertFalse($validator->php(['TestField' => '0']));
        $this->assertFalse($validator->php(['TestField' => ['value' => 0]]));
        $this->assertFalse($validator->php(['TestField' => ['value' => '0']]));
        // '1' passes required field validation
        $this->assertTrue($validator->php(['TestField' => '1']));
    }

    public static function provideAllowWhitespaceOnly(): array
    {
        return [
            'no-ws-false' => [
                'value' => 'abc',
                'allowWhitespaceOnly' => false,
                'expected' => true,
            ],
            'no-ws-true' => [
                'value' => 'abc',
                'allowWhitespaceOnly' => true,
                'expected' => true,
            ],
            'left-ws-false' => [
                'value' => ' abc',
                'allowWhitespaceOnly' => false,
                'expected' => true,
            ],
            'left-ws-true' => [
                'value' => ' abc',
                'allowWhitespaceOnly' => true,
                'expected' => true,
            ],
            'right-ws-false' => [
                'value' => 'abc ',
                'allowWhitespaceOnly' => false,
                'expected' => true,
            ],
            'right-ws-true' => [
                'value' => 'abc ',
                'allowWhitespaceOnly' => true,
                'expected' => true,
            ],
            'both-ws-false' => [
                'value' => ' abc ',
                'allowWhitespaceOnly' => false,
                'expected' => true,
            ],
            'both-ws-true' => [
                'value' => ' abc ',
                'allowWhitespaceOnly' => true,
                'expected' => true,
            ],
            'only-ws-false' => [
                'value' => ' ',
                'allowWhitespaceOnly' => false,
                'expected' => false,
            ],
            'only-ws-true' => [
                'value' => ' ',
                'allowWhitespaceOnly' => true,
                'expected' => true,
            ],
            'only-ws-nbsp-false' => [
                'value' => "\xc2\xa0",
                'allowWhitespaceOnly' => false,
                'expected' => false,
            ],
            'only-ws-nbsp-true' => [
                'value' => "\xc2\xa0",
                'allowWhitespaceOnly' => true,
                'expected' => true,
            ],
            'only-ws-unicode-false' => [
                // zero width no-break space
                'value' => "\u{2028}",
                'allowWhitespaceOnly' => false,
                'expected' => false,
            ],
            'only-ws-unicode-true' => [
                // zero width no-break space
                'value' => "\u{2028}",
                'allowWhitespaceOnly' => true,
                'expected' => true,
            ],
            'no-value-false' => [
                'value' => '',
                'allowWhitespaceOnly' => false,
                'expected' => false,
            ],
            'no-value-true' => [
                'value' => '',
                'allowWhitespaceOnly' => true,
                'expected' => false,
            ],
        ];
    }

    #[DataProvider('provideAllowWhitespaceOnly')]
    public function testAllowWhitespaceOnlyConfig(
        string $value,
        bool $allowWhitespaceOnly,
        bool $expected,
    ): void {
        $validator = new RequiredFieldsValidator(['TestField']);
        $this->assertSame(true, $validator->getAllowWhitespaceOnly());
        $field = new TextField('TestField');
        $field->setValue($value);
        $form = new Form(null, null, new FieldList([$field]), null, $validator);
        RequiredFieldsValidator::config()->set('allow_whitespace_only', $allowWhitespaceOnly);
        $result = $validator->validate($form);
        $this->assertEquals($expected, $result->isValid());
    }

    #[DataProvider('provideAllowWhitespaceOnly')]
    public function testAllowWhitespaceOnlySetter(
        string $value,
        bool $allowWhitespaceOnly,
        bool $expected,
    ): void {
        $validator = new RequiredFieldsValidator(['TestField']);
        $validator->setAllowWhitespaceOnly($allowWhitespaceOnly);
        $this->assertSame($allowWhitespaceOnly, $validator->getAllowWhitespaceOnly());
        $field = new TextField('TestField');
        $field->setValue($value);
        $form = new Form(null, null, new FieldList([$field]), null, $validator);
        $result = $validator->validate($form);
        $this->assertEquals($expected, $result->isValid());
        // assert that global config makes no difference
        RequiredFieldsValidator::config()->set('allow_whitespace_only', true);
        $result = $validator->validate($form);
        $this->assertEquals($expected, $result->isValid());
        RequiredFieldsValidator::config()->set('allow_whitespace_only', false);
        $result = $validator->validate($form);
        $this->assertEquals($expected, $result->isValid());
    }
}
