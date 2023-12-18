<?php

namespace LiamWiltshire\LaravelModelMeta\PHPStan;

use Illuminate\Database\Eloquent\Model;
use LiamWiltshire\LaravelModelMeta\Concerns\HasMeta;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use Tebex\Checkout\Models\RecurringPayment;

class ModelAttributeExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->getName() === (string)HasMeta::class) {
                return true;
            }
        }

        return false;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new MetaProperty($classReflection, $propertyName);
    }
}
