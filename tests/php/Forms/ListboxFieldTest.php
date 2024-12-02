<?php

namespace SilverStripe\Forms\Tests;

use SilverStripe\Forms\Tests\ListboxFieldTest\Article;
use SilverStripe\Forms\Tests\ListboxFieldTest\Tag;
use SilverStripe\Forms\Tests\ListboxFieldTest\TestObject;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\CSSContentParser;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Model\ArrayData;
use PHPUnit\Framework\Attributes\DataProvider;

class ListboxFieldTest extends SapphireTest
{
    protected static $fixture_file = 'ListboxFieldTest.yml';

    protected static $extra_dataobjects = [
        TestObject::class,
        Article::class,
        Tag::class,
    ];

    public function testFieldWithManyManyRelationship()
    {
        $articleWithTags = $this->objFromFixture(Article::class, 'articlewithtags');
        $tag1 = $this->objFromFixture(Tag::class, 'tag1');
        $tag2 = $this->objFromFixture(Tag::class, 'tag2');
        $tag3 = $this->objFromFixture(Tag::class, 'tag3');
        $field = new ListboxField("Tags", "Test field", DataObject::get(Tag::class)->map()->toArray());
        $field->setValue(null, $articleWithTags);

        $p = new CSSContentParser($field->Field());
        $tag1xml = $p->getByXpath('//option[@value=' . $tag1->ID . ']');
        $tag2xml = $p->getByXpath('//option[@value=' . $tag2->ID . ']');
        $tag3xml = $p->getByXpath('//option[@value=' . $tag3->ID . ']');
        $this->assertEquals('selected', (string)$tag1xml[0]['selected']);
        $this->assertEquals('selected', (string)$tag2xml[0]['selected']);
        $this->assertNull($tag3xml[0]['selected']);
    }

    public function testFieldWithDisabledItems()
    {
        $articleWithTags = $this->objFromFixture(Article::class, 'articlewithtags');
        $tag1 = $this->objFromFixture(Tag::class, 'tag1');
        $tag2 = $this->objFromFixture(Tag::class, 'tag2');
        $tag3 = $this->objFromFixture(Tag::class, 'tag3');
        $field = new ListboxField("Tags", "Test field", DataObject::get(Tag::class)->map()->toArray());
        $field->setValue(null, $articleWithTags);
        $field->setDisabledItems([$tag1->ID, $tag3->ID]);

        $p = new CSSContentParser($field->Field());
        $tag1xml = $p->getByXpath('//option[@value=' . $tag1->ID . ']');
        $tag2xml = $p->getByXpath('//option[@value=' . $tag2->ID . ']');
        $tag3xml = $p->getByXpath('//option[@value=' . $tag3->ID . ']');
        $this->assertEquals('selected', (string)$tag1xml[0]['selected']);
        $this->assertEquals('disabled', (string)$tag1xml[0]['disabled']);
        $this->assertEquals('selected', (string)$tag2xml[0]['selected']);
        $this->assertNull($tag2xml[0]['disabled']);
        $this->assertNull($tag3xml[0]['selected']);
        $this->assertEquals('disabled', (string)$tag3xml[0]['disabled']);
    }

    public function testSaveIntoNullValueWithMultipleOff()
    {
        $choices = ['a' => 'a value', 'b' => 'b value','c' => 'c value'];
        $field = new ListboxField('Choices', 'Choices', $choices);

        $obj = new TestObject();
        $field->setValue('a');
        $field->saveInto($obj);
        $field->setValue(null);
        $field->saveInto($obj);
        $this->assertNull($obj->Choices);
    }

    public function testSaveIntoNullValueWithMultipleOn()
    {
        $choices = ['a' => 'a value', 'b' => 'b value','c' => 'c value'];
        $field = new ListboxField('Choices', 'Choices', $choices);

        $obj = new TestObject();
        $field->setValue(['a', 'c']);
        $field->saveInto($obj);
        $field->setValue('');
        $field->saveInto($obj);
        $this->assertEquals('', $obj->Choices);
    }

    public function testSaveInto()
    {
        $choices = ['a' => 'a value', 'b' => 'b value','c' => 'c value'];
        $field = new ListboxField('Choices', 'Choices', $choices);

        $obj = new TestObject();
        $field->setValue('a');
        $field->saveInto($obj);
        $this->assertEquals('["a"]', $obj->Choices);
    }

    public function testSaveIntoMultiple()
    {
        $choices = ['a' => 'a value', 'b' => 'b value','c' => 'c value'];
        $field = new ListboxField('Choices', 'Choices', $choices);

        // As array
        $obj1 = new TestObject();
        $field->setValue(['a', 'c']);
        $field->saveInto($obj1);
        $this->assertEquals('["a","c"]', $obj1->Choices);

        // As string
        $obj2 = new TestObject();
        $obj2->Choices = '["a","c"]';
        $field->setValue(null, $obj2);
        $this->assertEquals(['a', 'c'], $field->Value());
        $field->saveInto($obj2);
        $this->assertEquals('["a","c"]', $obj2->Choices);
    }

    public function testSaveIntoManyManyRelation()
    {
        $article = $this->objFromFixture(Article::class, 'articlewithouttags');
        $articleWithTags = $this->objFromFixture(Article::class, 'articlewithtags');
        $tag1 = $this->objFromFixture(Tag::class, 'tag1');
        $tag2 = $this->objFromFixture(Tag::class, 'tag2');
        $field = new ListboxField("Tags", "Test field", DataObject::get(Tag::class)->map()->toArray());

        // Save new relations
        $field->setValue([$tag1->ID,$tag2->ID]);
        $field->saveInto($article);
        $article = DataObject::get_by_id(Article::class, $article->ID, false);
        $this->assertEquals([$tag1->ID, $tag2->ID], $article->Tags()->sort('ID')->column('ID'));

        // Remove existing relation
        $field->setValue([$tag1->ID]);
        $field->saveInto($article);
        $article = DataObject::get_by_id(Article::class, $article->ID, false);
        $this->assertEquals([$tag1->ID], $article->Tags()->sort('ID')->column('ID'));

        // Set NULL value
        $field->setValue(null);
        $field->saveInto($article);
        $article = DataObject::get_by_id(Article::class, $article->ID, false);
        $this->assertEquals([], $article->Tags()->sort('ID')->column('ID'));
    }

    public function testFieldRenderingMultipleOff()
    {
        $choices = ['a' => 'a value', 'b' => 'b value','c' => 'c value'];
        $field = new ListboxField('Choices', 'Choices', $choices);
        $field->setValue('a');
        $parser = new CSSContentParser($field->Field());
        $optEls = $parser->getBySelector('option');
        $this->assertEquals(3, count($optEls ?? []));
        $this->assertEquals('selected', (string)$optEls[0]['selected']);
        $this->assertEquals('', (string)$optEls[1]['selected']);
        $this->assertEquals('', (string)$optEls[2]['selected']);
    }

    public function testFieldRenderingMultipleOn()
    {
        $choices = ['a' => 'a value', 'b' => 'b value','c' => 'c value'];
        $field = new ListboxField('Choices', 'Choices', $choices);
        $field->setValue(['a', 'c']);
        $parser = new CSSContentParser($field->Field());
        $optEls = $parser->getBySelector('option');
        $this->assertEquals(3, count($optEls ?? []));
        $this->assertEquals('selected', (string)$optEls[0]['selected']);
        $this->assertEquals('', (string)$optEls[1]['selected']);
        $this->assertEquals('selected', (string)$optEls[2]['selected']);
    }

    public static function provideSetValueValidation(): array
    {
        return [
            'valid-single' => [
                'value' => 1,
                'expected' => true,
            ],
            'valid-single-array' => [
                'value' => [1],
                'expected' => true,
            ],
            'valid-multi-array' => [
                'value' => [1, 2],
                'expected' => true,
            ],
            'invalid-single' => [
                'value' => 4,
                'expected' => false,
            ],
            'invalid-array' => [
                'value' => [4],
                'expected' => false,
            ],
            'invalid-mix' => [
                'value' => [1, 4],
                'expected' => false,
            ],
        ];
    }

    /**
     * @useDatabase false
     */
    #[DataProvider('provideSetValueValidation')]
    public function testSetValueValidation(int|array $value, bool $expected): void
    {
        $field = new ListBoxField('Test', 'Testing', [1 => 'One', 2 => 'Two']);
        $field->setValue($value);
        $actual = $field->validate()->isValid();
        $this->assertSame($expected, $actual);
    }

    public function testValidationWithDataList()
    {
        $tag1 = $this->objFromFixture(Tag::class, 'tag1');
        $tag2 = $this->objFromFixture(Tag::class, 'tag2');
        $tag3 = $this->objFromFixture(Tag::class, 'tag3');
        $field = ListboxField::create('Test', 'Testing', DataObject::get(Tag::class)->map()->toArray());
        $invalidID = Tag::get()->max('ID') + 1;
        $field->setValue($tag1->ID);
        $this->assertTrue($field->validate()->isValid());
        $field->setValue([$tag1->ID, $tag2->ID]);
        $this->assertTrue($field->validate()->isValid());
        $field->setValue($invalidID);
        $this->assertFalse($field->validate()->isValid());
        $field->setValue([$tag1->ID, $invalidID]);
        $this->assertFalse($field->validate()->isValid());
    }

    public function testFieldWithDefaultItems()
    {
        $articleWithTags = $this->objFromFixture(Article::class, 'articlewithtags');
        $tag1 = $this->objFromFixture(Tag::class, 'tag1');
        $tag2 = $this->objFromFixture(Tag::class, 'tag2');
        $tag3 = $this->objFromFixture(Tag::class, 'tag3');
        $field = new ListboxField("Tags", "Test field", DataObject::get(Tag::class)->map()->toArray());
        $field->setDefaultItems([$tag1->ID, $tag2->ID]);
        $field->setValue(null, $articleWithTags);
        $field->setDisabledItems([$tag1->ID, $tag3->ID]);

        // Confirm that tag1 and tag2 are selected
        $p = new CSSContentParser($field->Field());
        $tag1xml = $p->getByXpath('//option[@value=' . $tag1->ID . ']');
        $tag2xml = $p->getByXpath('//option[@value=' . $tag2->ID . ']');
        $tag3xml = $p->getByXpath('//option[@value=' . $tag3->ID . ']');
        $this->assertEquals('selected', (string)$tag1xml[0]['selected']);
        $this->assertEquals('selected', (string)$tag2xml[0]['selected']);
        $this->assertNull($tag3xml[0]['selected']);

        // Confirm that tag1 and tag2 are listed in the readonly variation
        $p = new CSSContentParser($field->performReadonlyTransformation()->Field());
        $this->assertEquals(
            'Tag 1, Tag 2',
            trim(preg_replace('/\s+/', ' ', $p->getByXpath('//span')[0] ?? '') ?? '')
        );
        $this->assertEquals(
            '1, 2',
            '' . $p->getByXpath('//input')[0]['value']
        );
    }

    public static function provideGetValueForValidation(): array
    {
        return [
            'not-array' => [
                'value' => 'cat',
                'expected' => ['cat'],
            ],
            'array' => [
                'value' => ['cat'],
                'expected' => ['cat'],
            ],
            'null' => [
                'value' => null,
                'expected' => null,
            ],
        ];
    }

    /**
     * @useDatabase false
     */
    #[DataProvider('provideGetValueForValidation')]
    public function testGetValueForValidation(mixed $value, mixed $expected): void
    {
        $field = new ListboxField('Test');
        $field->setValue($value);
        $actual = $field->getValueForValidation($value);
        $this->assertSame($expected, $actual);
    }
}
