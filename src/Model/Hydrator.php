<?php

namespace Model;

use ReflectionClass;
use ReflectionException;

class Hydrator
{
    /**
     * @throws ReflectionException
     */
    public function hydrate(array $data, $object)
    {
        $reflectionClass = new ReflectionClass(get_class($object));

        foreach ($data as $key => $value) {
            $method_name = "set" . ucfirst($key);
            if ($reflectionClass->hasMethod($method_name)) {
                $method = $reflectionClass->getMethod($method_name);
                $method->invoke($object, $value);
            }
        }

        return $object;
    }
}