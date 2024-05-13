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
            $key = preg_replace_callback('/([_])([a-z])/', function ($match) {
                return strtoupper($match[2]);
            }, $key);
            $methodName = "set" . ucfirst($key);
//            echo "<br/> methode : ".$methodName;
            if ($reflectionClass->hasMethod($methodName)) {
//                echo " ==> OK";
                $method = $reflectionClass->getMethod($methodName);
                $method->invoke($object, $value);
            }
        }

        return $object;
    }
}