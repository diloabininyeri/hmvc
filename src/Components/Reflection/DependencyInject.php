<?php


namespace App\Components\Reflection;

use App\Components\Http\Request;
use App\Interfaces\RequestInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class DependencyInject
 * @package App\Components\Reflection
 */
class DependencyInject
{
    /**
     * @var object
     */
    private object $object;
    /**
     * @var string
     */
    private string $method;

    /**
     * @var string
     */
    private string  $class;

    /**
     * @var RequestInterface|null
     */
    private ?RequestInterface $request=null;

    /**
     * @param object $object
     * @return DependencyInject
     */
    public function setObject(object $object): DependencyInject
    {
        $this->object = $object;
        return $this;
    }

    /**
     * @param string $method
     * @return DependencyInject
     */
    public function setMethod(string $method): DependencyInject
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return ReflectionParameter[]
     * @throws ReflectionException
     */
    public function getParameters():array
    {
        $reflectionMethod = new ReflectionMethod(get_class($this->object) ?: $this->class, $this->method);
        return $reflectionMethod->getParameters();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getAllClass(): array
    {
        $params = [];
        foreach ($this->getParameters() as $parameter) {
            if (method_exists($parameter->getClass(), 'getName')) {
                $params[] = $parameter->getClass()->getName();
            }
        }
        return $params;
    }


    /**
     * @return mixed|null
     * @throws ReflectionException
     */
    public function getFirstInjectClass():?string
    {
        return $this->getAllClass()[0] ?? null;
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function call()
    {
        return call_user_func_array([$this->object,$this->method], $this->createObjectParameters());
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function isExistsRequestClass():bool
    {
        return $this->findIndexRequestParameter($this->createParametersAsObjects())!==null;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function createParametersAsObjects(): array
    {
        return array_map(fn ($cl) =>new $cl, $this->getAllClass());
    }
    /**
     * @return array
     * @throws ReflectionException
     */
    private function createObjectParameters():array
    {
        $parameters=$this->createParametersAsObjects();
        if ($this->request) {
            $findRequestIndex= $this->findIndexRequestParameter($parameters);
            if ($findRequestIndex) {
                $parameters[$findRequestIndex] = $this->request;
            }
        }

        return $parameters;
    }

    /**
     * @param string $class
     * @return DependencyInject
     */
    public function setClass(string $class): DependencyInject
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param Request $request
     * @return DependencyInject
     */
    public function setRequest(Request $request): DependencyInject
    {
        $this->request = $request;
        return $this;
    }


    /**
     * @return bool
     * @throws ReflectionException
     */
    public function isExistsClasses():bool
    {
        return !empty($this->getParameters());
    }

    /**
     * @param array $objects
     * @return int|null
     */
    private function findIndexRequestParameter(array $objects):?int
    {
        foreach ($objects as $key=>$object) {
            if ($parent = get_parent_class($object)) {
                if ($parent === Request::class) {
                    return $key;
                }
            }
            if (get_class($object)===Request::class) {
                return $key;
            }
        }
        return  null;
    }

    /**
     * @return object
     */
    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
