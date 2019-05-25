<?php


namespace oukhennicheabdelkrim\DIC\Definition;

use oukhennicheabdelkrim\DIC\Definition\Exceptions;
use Psr\Container\ContainerInterface;

/**
 * Class Resolver
 * @package oukhennicheabdelkrim\DIC\Definition
 */
class Resolver
{

    /**
     * @var CacheInstanceInterface
     */
    private $cacheInstance;
    /**
     * @var
     */
    private $registredResolveStore;


    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Resolver constructor.
     * @param CacheInstanceInterface $cacheInstance
     * @param ContainerInterface $container
     */
    public function __construct(CacheInstanceInterface $cacheInstance, ContainerInterface $container)
    {
        $this->cacheInstance = $cacheInstance;
        $this->registredResolveStore = [];
        $this->container = $container;
    }

    /**********************************************************************************

     -> Public methods

    /**********************************************************************************

     *
    /**
     * Resolve
     * @param $id
     * @param bool $inResolves
     * return mixed (instance)
     * @throws NotFoundException
     */

    public function resolve($id, $cached = true)
    {
        $resolve = $this->getResolve($id);
        if ($resolve!==null) return $this->resolveRegistered($id, $cached);
        $this->register($id,$this->createResolve($id));
        return $this->resolveRegistered($id, $cached);
    }


    /** register
     * @param $id
     * @param $resolve
     * @param bool $singleton
     */
    public function register($id, $resolve)
    {
        $this->registredResolveStore[$id] = $resolve;
    }


    /**
     * @param $id
     * @return bool
     */
    public function canResolve($id): bool
    {
        $resolve = $this->getResolve($id);
        if (isset($resolve)) {
            return true;
        } else {
            $reflectionClass = $this->getReflectionCalss($id);
            return $reflectionClass !== null && $this->isInstanciable($reflectionClass);
        }

    }




    /**********************************************************************************

    -> Private methods

    /**********************************************************************************


    /**
     * @param $id
     * @return bool
     */
    private function isInCacheInstancies($id): bool
    {
        return $this->cacheInstance->has($id);
    }


    /**
     * @param $id
     * @param $instance
     */
    private function setInCacheInstancies($id, $instance)
    {
        $this->cacheInstance->put($id, $instance);
    }

    /**
     * @param $id
     * @return resolve or null if resolve is not registered
     */
    private function getResolve($id)
    {
        return isset($this->registredResolveStore[$id]) ? $this->registredResolveStore[$id] : null;
    }


    /**
     * @param $id
     * @return mixed
     */
    private function getInstanceFromCache(string $id)
    {
        return $this->cacheInstance->get($id);
    }


    /**
     * @param string $id
     * @param $fromCache
     * @return mixed
     */
    private function resolveRegistered(string $id, $fromCache)
    {

        $resolve = $this->getResolve($id);
        if ($fromCache) {
            if (!$this->isInCacheInstancies($id))
                $this->setInCacheInstancies($id, $this->getNewInstance($resolve));
            return $this->getInstanceFromCache($id);
        }
        else
            return $this->getNewInstance($resolve);

    }

    /**
     * @param $className
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\NotInstantiableExecption
     * create resolve
     */

    private function createResolve($className){

        /** @var \ReflectionClass $reflectionClass */
        $reflectionClass = $this->getReflectionCalss($className);
        if ($reflectionClass !== null) {
            if ($this->isInstanciable($reflectionClass)) {
                $params = [];
                $constructor = $reflectionClass->getConstructor();
                if ($constructor !== null) {
                    $reflectionParams = $reflectionClass->getConstructor()->getParameters();
                    foreach ($reflectionParams as $reflectionParam) {
                        $type = $reflectionParam->getType();
                        if (isset($type))
                            $params[] = $this->resolve($type->getName());
                        else
                            $params[] = $this->getDefaultValue($reflectionParam,$className);
                    }
                }
                return function () use ($reflectionClass, $params) {
                    return $reflectionClass->newInstanceArgs($params);
                };

            } else {
                throw new Exceptions\NotInstantiableExecption("DIC : $className  is not instantiable");
            }
        } else {
            throw new Exceptions\NotFoundException("DIC : Can not found '$className' class");
        }
    }


    /**
     * @param $className
     * @return mixed
     */
    private function getReflectionCalss($className)
    {
        try {
            return new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            return null;
        }

    }

    /**
     * @param \ReflectionParameter $p
     * @param $className
     * @return mixed
     * @throws Exceptions\NoDefaultParams
     */
    private function getDefaultValue (\ReflectionParameter $p, $className)
    {
        try{
            return $p->getDefaultValue();
        }
        catch (\ReflectionException $e){
            throw new Exceptions\NoDefaultParams("DIC Instantiation error :Can not found default value of '{$p->getName()}' parameter in constructor of '$className'.");
        }
    }


    /**
     * @param \ReflectionClass $reflectionClass
     * @return bool
     */
    private function isInstanciable(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->isInstantiable();
    }


    /**
     * @param $resolve
     * @return mixed
     */
    private function getNewInstance($resolve)
    {
        if (is_callable($resolve)) {
            return $resolve($this->container);
        } else {
            return $resolve;
        }
    }


}
