<?php

namespace SilverStripe\Forms\FieldValidatorConverter;

interface FieldValidatorConverterInterface
{
    public function internalToFrontend(mixed $value): ?string;

    public function getFieldValidatorConverter(): callable;
}
