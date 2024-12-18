<?php

namespace SilverStripe\ORM\Tests\DataObjectTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Validation\ValidationResult;

class ValidatedObject extends DataObject implements TestOnly
{
    private static $table_name = 'DataObjectTest_ValidatedObject';

    private static $db = [
        'Name' => 'Varchar(50)'
    ];

    public function validate(): ValidationResult
    {
        $result = ValidationResult::create();
        if (empty($this->Name)) {
            $result->addError("This object needs a name. Otherwise it will have an identity crisis!");
        }
        return $result;
    }
}
