<?php

namespace Minime\Annotations;

use Minime\Annotations\Interfaces\ParserInterface;
use Minime\Annotations\Interfaces\ReaderInterface;
use Minime\Annotations\Interfaces\CacheInterface;
use Minime\Annotations\Cache\FileCache;

/**
 * This class is the primary entry point to read annotations
 *
 * @package Minime\Annotations
 */
class Reader implements ReaderInterface
{
    /**
     * @var Interfaces\ParserInterface
     */
    protected $parser;

    /**
     * @var Interfaces\CacheInterface
     */
    protected $cache;

    /**
     * @param ParserInterface $parser
     */
    public function __construct(ParserInterface $parser, CacheInterface $cache = null)
    {
        $this->parser = $parser;
        $this->cache  = $cache;
    }

    /**
     * Retrieve all annotations from a given class
     *
     * @param  mixed                                                  $class Full qualified class name or object
     * @return \Minime\Annotations\Interfaces\AnnotationsBagInterface Annotations collection
     * @throws \ReflectionException                                   If class is not found
     */
    public function getClassAnnotations($class)
    {
        return $this->getAnnotations(new \ReflectionClass($class));
    }

    /**
     * Retrieve all annotations from a given property of a class
     *
     * @param  mixed                                                  $class    Full qualified class name or object
     * @param  string                                                 $property Property name
     * @return \Minime\Annotations\Interfaces\AnnotationsBagInterface Annotations collection
     * @throws \ReflectionException                                   If property is undefined
     */
    public function getPropertyAnnotations($class, $property)
    {
        return $this->getAnnotations(new \ReflectionProperty($class, $property));
    }

    /**
     * Retrieve all annotations from a given method of a class
     *
     * @param  mixed                                                  $class  Full qualified class name or object
     * @param  string                                                 $method Method name
     * @return \Minime\Annotations\Interfaces\AnnotationsBagInterface Annotations collection
     * @throws \ReflectionException                                   If method is undefined
     */
    public function getMethodAnnotations($class, $method)
    {
        return $this->getAnnotations(new \ReflectionMethod($class, $method));
    }

    /**
     * Retrieve annotations from docblock of a given reflector
     *
     * @param  \Reflector                                             $Reflection Reflector object
     * @return \Minime\Annotations\Interfaces\AnnotationsBagInterface Annotations collection
     */
    public function getAnnotations(\Reflector $Reflection)
    {
        $doc = $Reflection->getDocComment();
        if($this->cache) {
            $key = $this->cache->getKey($doc);
            $ast = $this->cache->get($key);
            if(! $ast) {
                $ast = $this->parser->parse($doc);
                $this->cache->set($key, $ast);
            }
        }
        else {
            $ast = $this->parser->parse($doc);
        }

        return new AnnotationsBag($ast, $this->parser->getRules());
    }
}