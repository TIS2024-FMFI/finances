<?php

namespace Tests\Util;

use ReflectionClass;

/**
 * A helper class to access private and protected members during tests.
 * 
 * This class provides methods to:
 *      - get the value of a hidden static property
 */
class HiddenMembersAccessor
{
    /**
     * Get the value of a hidden static property of a class.
     * 
     * Source: https://stackoverflow.com/questions/249664/best-practices-to-test-protected-methods-with-phpunit
     * @param string $className
     * the name of the class whose property value to return
     * @param string $propertyName
     * the name of the property whose value to return
     * @return mixed
     * the property value
     */
    public static function getHiddenStaticProperty(string $className, string $propertyName)
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue();
    }
}