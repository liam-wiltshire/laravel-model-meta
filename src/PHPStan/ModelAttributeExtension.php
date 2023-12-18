<?php

namespace LiamWiltshire\LaravelModelMeta\PHPStan;

use Illuminate\Database\Eloquent\Model;
use LiamWiltshire\LaravelModelMeta\Concerns\HasMeta;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use Tebex\Checkout\Models\RecurringPayment;

class ModelAttributeExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(\PHPStan\Reflection\ClassReflection $classReflection, string $propertyName): bool
    {
        foreach ($classReflection->getTraits() as $trait) {
            if($trait->getName() === (string)HasMeta::class) {
                return true;
            }
        }

        return false;
    }

    public function getProperty(\PHPStan\Reflection\ClassReflection $classReflection, string $propertyName): \PHPStan\Reflection\PropertyReflection
    {
        return new MetaProperty($classReflection, $propertyName);
    }
}
