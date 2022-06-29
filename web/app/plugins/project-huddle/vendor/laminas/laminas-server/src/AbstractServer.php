<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-server/blob/master/LICENSE.md New BSD License
 */

namespace ProjectHuddle\Vendor\Laminas\Server;

use ReflectionClass;

/**
 * Abstract Server implementation
 */
abstract class AbstractServer implements Server
{
    /**
     * @var bool Flag; whether or not overwriting existing methods is allowed
     */
    protected $overwriteExistingMethods = false;

    /**
     * @var Definition
     */
    protected $table;

    /**
     * Constructor
     *
     * Setup server description
     *
     */
    public function __construct()
    {
        $this->table = new Definition();
        $this->table->setOverwriteExistingMethods($this->overwriteExistingMethods);
    }

    /**
     * Returns a list of registered methods
     *
     * Returns an array of method definitions.
     *
     * @return Definition
     */
    public function getFunctions()
    {
        return $this->table;
    }

    /**
     * Build callback for method signature
     *
     * @param  Reflection\AbstractFunction $reflection
     * @return Method\Callback
     */
    protected function buildCallback(Reflection\AbstractFunction $reflection)
    {
        $callback = new Method\Callback();
        if ($reflection instanceof Reflection\ReflectionMethod) {
            $callback->setType($reflection->isStatic() ? 'static' : 'instance')
                     ->setClass($reflection->getDeclaringClass()->getName())
                     ->setMethod($reflection->getName());
        } elseif ($reflection instanceof Reflection\ReflectionFunction) {
            $callback->setType('function')
                     ->setFunction($reflection->getName());
        }
        return $callback;
    }

    /**
     * Build callback for method signature
     *
     * @deprecated Since 2.7.0; method will be removed in 3.0, use
     *             buildCallback() instead.
     * @param  Reflection\AbstractFunction $reflection
     * @return Method\Callback
     */
    // @codingStandardsIgnoreStart
    protected function _buildCallback(Reflection\AbstractFunction $reflection)
    {
    // @codingStandardsIgnoreEnd
        return $this->buildCallback($reflection);
    }

    /**
     * Build a method signature
     *
     * @param  Reflection\AbstractFunction $reflection
     * @param  null|string|object $class
     * @return Method\Definition
     * @throws Exception\RuntimeException on duplicate entry
     */
    final protected function buildSignature(Reflection\AbstractFunction $reflection, $class = null)
    {
        $ns         = $reflection->getNamespace();
        $name       = $reflection->getName();
        $method     = empty($ns) ? $name : $ns . '.' . $name;

        if (! $this->overwriteExistingMethods && $this->table->hasMethod($method)) {
            throw new Exception\RuntimeException('Duplicate method registered: ' . $method);
        }

        $definition = new Method\Definition();
        $definition->setName($method)
                   ->setCallback($this->buildCallback($reflection))
                   ->setMethodHelp($reflection->getDescription())
                   ->setInvokeArguments($reflection->getInvokeArguments());

        foreach ($reflection->getPrototypes() as $proto) {
            $prototype = new Method\Prototype();
            $prototype->setReturnType($this->_fixType($proto->getReturnType()));
            foreach ($proto->getParameters() as $parameter) {
                $param = new Method\Parameter([
                    'type'     => $this->_fixType($parameter->getType()),
                    'name'     => $parameter->getName(),
                    'optional' => $parameter->isOptional(),
                ]);
                if ($parameter->isDefaultValueAvailable()) {
                    $param->setDefaultValue($parameter->getDefaultValue());
                }
                $prototype->addParameter($param);
            }
            $definition->addPrototype($prototype);
        }
        if (is_object($class)) {
            $definition->setObject($class);
        }
        $this->table->addMethod($definition);
        return $definition;
    }

    /**
     * Build a method signature
     *
     * @deprecated Since 2.7.0; method will be removed in 3.0, use
     *             buildSignature() instead.
     * @param  Reflection\AbstractFunction $reflection
     * @param  null|string|object $class
     * @return Method\Definition
     * @throws Exception\RuntimeException on duplicate entry
     */
    // @codingStandardsIgnoreStart
    protected function _buildSignature(Reflection\AbstractFunction $reflection, $class = null)
    {
    // @codingStandardsIgnoreEnd
        return $this->buildSignature($reflection, $class);
    }

    /**
     * Dispatch method
     *
     * @deprecated Since 2.7.0; method will be renamed to remove underscore
     *     prefix in 3.0.
     * @param  Method\Definition $invokable
     * @param  array $params
     * @return mixed
     */
    // @codingStandardsIgnoreStart
    protected function _dispatch(Method\Definition $invokable, array $params)
    {
    // @codingStandardsIgnoreEnd
        $callback = $invokable->getCallback();
        $type     = $callback->getType();

        if ('function' == $type) {
            $function = $callback->getFunction();
            return call_user_func_array($function, $params);
        }

        $class  = $callback->getClass();
        $method = $callback->getMethod();

        if ('static' == $type) {
            return call_user_func_array([$class, $method], $params);
        }

        $object = $invokable->getObject();
        if (! is_object($object)) {
            $invokeArgs = $invokable->getInvokeArguments();
            if (! empty($invokeArgs)) {
                $reflection = new ReflectionClass($class);
                $object     = $reflection->newInstanceArgs($invokeArgs);
            } else {
                $object = new $class;
            }
        }
        return call_user_func_array([$object, $method], $params);
    }

    // @codingStandardsIgnoreStart
    /**
     * Map PHP type to protocol type
     *
     * @deprecated Since 2.7.0; method will be renamed to remove underscore
     *     prefix in 3.0.
     * @param  string $type
     * @return string
     */
    abstract protected function _fixType($type);
    // @codingStandardsIgnoreEnd
}
