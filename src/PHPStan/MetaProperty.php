<?php

namespace LiamWiltshire\LaravelModelMeta\PHPStan;

use Illuminate\Database\Eloquent\Model;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class MetaProperty implements PropertyReflection
{

    private $classReflection;
    private $propertyName;

    public function __construct(ClassReflection $classReflection, string $propertyName)
    {
        $this->classReflection = $classReflection;
        $this->propertyName = $propertyName;
    }
    
    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return false;
    }

    public function isInternal(): TrinaryLogic
    {
        return false;
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return true;
    }

    public function getReadableType(): Type
    {
        return new MixedType();
    }

    public function getWritableType(): Type
    {
        return new MixedType();
    }

    public function getDeclaringClass(): \PHPStan\Reflection\ClassReflection
    {
        return $this->classReflection;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }
}
