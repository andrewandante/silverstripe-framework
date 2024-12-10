<?php

namespace SilverStripe\Forms\Tests;

use IntlDateFormatter;
use LogicException;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DateField_Disabled;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use ReflectionMethod;
use PHPUnit\Framework\Attributes\DataProvider;

class DateFieldTest extends SapphireTest
{
    protected function setUp(): void
    {
        parent::setUp();
        i18n::set_locale('en_NZ');
        DBDatetime::set_mock_now('2011-02-01 8:34:00');
    }

    public function testSetMinDate()
    {
        $f = (new DateField('Date'))->setMinDate('2009-03-31');
        $this->assertEquals($f->getMinDate(), '2009-03-31');
        $f = (new DateField('Date'))->setMinDate('invalid');
        $this->assertNull($f->getMinDate());
    }

    public function testSetMaxDate()
    {
        $f = (new DateField('Date'))->setMaxDate('2009-03-31');
        $this->assertEquals($f->getMaxDate(), '2009-03-31');
        $f = (new DateField('Date'))->setMaxDate('invalid');
        $this->assertNull($f->getMaxDate());
    }

    public function testValidateMinDate()
    {
        $dateField = new DateField('Date');
        $dateField->setMinDate('2009-03-31');
        $dateField->setValue('2010-03-31');
        $this->assertTrue($dateField->validate()->isValid());

        $dateField = new DateField('Date');
        $dateField->setMinDate('2009-03-31');
        $dateField->setValue('1999-03-31');
        $this->assertFalse($dateField->validate()->isValid());

        $dateField = new DateField('Date');
        $dateField->setMinDate('2009-03-31');
        $dateField->setValue('2009-03-31');
        $this->assertTrue($dateField->validate()->isValid());
    }

    public function testValidateMinDateStrtotime()
    {
        $f = new DateField('Date');
        $f->setMinDate('-7 days');
        $f->setValue(date('Y-m-d', strtotime('-8 days', DBDatetime::now()->getTimestamp())));
        $this->assertFalse($f->validate()->isValid());

        $f = new DateField('Date');
        $f->setMinDate('-7 days');
        $f->setValue(date('Y-m-d', strtotime('-7 days', DBDatetime::now()->getTimestamp())));
        $this->assertTrue($f->validate()->isValid());
    }

    public function testValidateMaxDateStrtotime()
    {
        $f = new DateField('Date');
        $f->setMaxDate('7 days');
        $f->setValue(date('Y-m-d', strtotime('8 days', DBDatetime::now()->getTimestamp())));
        $this->assertFalse($f->validate()->isValid());

        $f = new DateField('Date');
        $f->setMaxDate('7 days');
        $f->setValue(date('Y-m-d', strtotime('7 days', DBDatetime::now()->getTimestamp())));
        $this->assertTrue($f->validate()->isValid());
    }

    public function testValidateMaxDate()
    {
        $f = new DateField('Date');
        $f->setMaxDate('2009-03-31');
        $f->setValue('1999-03-31');
        $this->assertTrue($f->validate()->isValid());

        $f = new DateField('Date');
        $f->setMaxDate('2009-03-31');
        $f->setValue('2010-03-31');
        $this->assertFalse($f->validate()->isValid());

        $f = new DateField('Date');
        $f->setMaxDate('2009-03-31');
        $f->setValue('2009-03-31');
        $this->assertTrue($f->validate()->isValid());
    }

    public function testConstructorWithoutArgs()
    {
        $f = new DateField('Date');
        $this->assertEquals($f->dataValue(), null);
    }

    public function testConstructorWithDateString()
    {
        $f = new DateField('Date', 'Date', '29/03/2003');
        $this->assertEquals('29/03/2003', $f->dataValue());
        $f = new DateField('Date', 'Date', '2003-03-29 12:23:00');
        $this->assertEquals('2003-03-29', $f->dataValue());
    }

    public function testSetValue()
    {
        $f = (new DateField('Date', 'Date'))->setValue('notadate');
        $this->assertNull($f->Value(), 'Invalid input ignored');

        $f = (new DateField('Date', 'Date'))->setValue('-1 day');
        $this->assertEquals($f->Value(), '2011-01-31', 'Relative dates accepted');

        $f = (new DateField('Date', 'Date'))->setValue('2011-01-31');
        $this->assertEquals($f->Value(), '2011-01-31', 'ISO format accepted');

        $f = (new DateField('Date', 'Date'))->setValue('2011-01-31 23:59:59');
        $this->assertEquals($f->Value(), '2011-01-31', 'ISO format with time accepted');

        $f->setValue(null);
        $this->assertNull($f->Value());
    }

    public function testSetValueWithLocalisedDateString()
    {
        $f = new DateField('Date', 'Date');
        $f->setHTML5(false);
        $f->setSubmittedValue('29/03/2003');
        $this->assertEquals($f->dataValue(), '2003-03-29');
    }

    public function testConstructorWithIsoDate()
    {
        // used by Form->loadDataFrom()
        $f = new DateField('Date', 'Date', '2003-03-29');
        $this->assertEquals($f->dataValue(), '2003-03-29');
    }

    public function testValidateDMY()
    {
        // Constructor only accepts iso8601
        $f = new DateField('Date', 'Date', '29/03/2003');
        $this->assertSame('29/03/2003', $f->getValue());
        $this->assertFalse($f->validate()->isValid());

        // Set via submitted value (localised) accepts this, convert it to iso8601 though
        // though it may change the date if the local format is ambiguous
        $f = new DateField('Date', 'Date');
        $f->setSubmittedValue('29/03/2003');
        $this->assertSame('2034-08-24', $f->getValue());
        $this->assertTrue($f->validate()->isValid());

        // iso8601 is accepted
        $f = new DateField('Date', 'Date', '2003-03-29');
        $this->assertSame('2003-03-29', $f->getValue());
        $this->assertTrue($f->validate()->isValid());
    }

    public function testFormatEnNz()
    {
        /* We get YYYY-MM-DD format as the data value for DD/MM/YYYY input value */
        $f = new DateField('Date', 'Date');
        $f->setHTML5(false);
        $f->setSubmittedValue('29/03/2003');
        $this->assertEquals($f->dataValue(), '2003-03-29');
    }

    public function testSetLocale()
    {
        // should get en_NZ by default through setUp()
        i18n::set_locale('de_DE');
        $f = new DateField('Date', 'Date', '29/03/2003');
        $f->setHTML5(false);
        $f->setValue('29.06.2006');
        $this->assertEquals($f->dataValue(), '2006-06-29');
    }

    /**
     * Note: This is mostly tested for legacy reasons
     */
    public function testMDYFormat()
    {
        $dateField = new DateField('Date', 'Date');
        $dateField->setHTML5(false);
        $dateField->setDateFormat('d/M/y');
        $dateField->setSubmittedValue('31/03/2003');
        $this->assertEquals(
            '2003-03-31',
            $dateField->dataValue(),
            "We get MM-DD-YYYY format as the data value for YYYY-MM-DD input value"
        );

        $dateField2 = new DateField('Date', 'Date');
        $dateField2->setHTML5(false);
        $dateField2->setDateFormat('d/M/y');
        $dateField2->setSubmittedValue('04/3/03');
        $this->assertEquals(
            $dateField2->dataValue(),
            '2003-03-04',
            "Even if input value hasn't got leading 0's in it we still get the correct data value"
        );
    }

    public function testHtml5WithCustomFormatThrowsException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/Please opt-out .* if using setDateFormat/');
        $dateField = new DateField('Date', 'Date');
        $dateField->setValue('2010-03-31');
        $dateField->setDateFormat('d/M/y');
        $dateField->Value();
    }

    public function testHtml5WithCustomDateLengthThrowsException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/Please opt-out .* if using setDateLength/');
        $dateField = new DateField('Date', 'Date');
        $dateField->setValue('2010-03-31');
        $dateField->setDateLength(IntlDateFormatter::MEDIUM);
        $dateField->Value();
    }

    public function testHtml5WithCustomLocaleThrowsException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/Please opt-out .* if using setLocale/');
        $dateField = new DateField('Date', 'Date');
        $dateField->setValue('2010-03-31');
        $dateField->setLocale('de_DE');
        $dateField->Value();
    }

    public function testGetDateFormatHTML5()
    {
        $field = new DateField('Date');
        $field->setHTML5(true);
        $this->assertSame(DBDate::ISO_DATE, $field->getDateFormat());
    }

    public function testGetDateFormatViaSetter()
    {
        $field = new DateField('Date');
        $field->setHTML5(false);
        $field->setDateFormat('d-m-Y');
        $this->assertSame('d-m-Y', $field->getDateFormat());
    }

    public function testGetAttributes()
    {
        $field = new DateField('Date');
        $field
            ->setHTML5(true)
            ->setMinDate('1980-05-10')
            ->setMaxDate('1980-05-20');

        $result = $field->getAttributes();
        $this->assertSame('1980-05-10', $result['min']);
        $this->assertSame('1980-05-20', $result['max']);
    }

    public function testSetSubmittedValueNull()
    {
        $field = new DateField('Date');
        $field->setSubmittedValue(false);
        $this->assertNull($field->Value());
    }

    public function testPerformReadonlyTransformation()
    {
        $field = new DateField('Date');
        $result = $field->performReadonlyTransformation();
        $this->assertInstanceOf(DateField_Disabled::class, $result);
        $this->assertTrue($result->isReadonly());
    }

    public function testValidateWithoutValueReturnsTrue()
    {
        $field = new DateField('Date');
        $this->assertTrue($field->validate()->isValid());
    }

    public static function provideTidyInternal(): array
    {
        return [
            'date-only' => [
                'date' => '1980-05-10',
                'returnNullOnFailure' => false,
                'expected' => '1980-05-10',
            ],
            'remove-time' => [
                'date' => '1980-05-10 12:34:56',
                'returnNullOnFailure' => false,
                'expected' => '1980-05-10',
            ],
            'null' => [
                'date' => null,
                'returnNullOnFailure' => false,
                'expected' => null,
            ],
            'cannot-parse-not-null-on-failure' => [
                'date' => 'fish',
                'returnNullOnFailure' => false,
                'expected' => 'fish',
            ],
            'cannot-parse-null-on-failure' => [
                'date' => 'fish',
                'returnNullOnFailure' => true,
                'expected' => null,
            ],
        ];
    }

    #[DataProvider('provideTidyInternal')]
    public function testTidyInternal(?string $date, bool $returnNullOnFailure, ?string $expected): void
    {
        $field = new DateField('Date');
        $method = new ReflectionMethod($field, 'tidyInternal');
        $method->setAccessible(true);
        $actual = $method->invoke($field, $date, $returnNullOnFailure);
        $this->assertEquals($expected, $actual);
    }
}
