<?php

declare(strict_types=1);

namespace Duyler\Web;

use Duyler\Web\Build\Controller;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;

class ArgumentBuilder
{
    /**
     * @throws ReflectionException
     */
    public function build(Controller $controller, array $arguments = []): array
    {
        if (is_string($controller->handler)) {
            $reflection = new ReflectionClass($controller->handler);
            $methodReflection = null;
            foreach ($reflection->getMethods() as $method) {
                if ($method->getName() === $controller->getMethod()) {
                    $methodReflection = $method;
                    break;
                }
            }

            if (null === $methodReflection) {
                throw new InvalidArgumentException(
                    'Method ' . $controller->getMethod() . ' not found in ' . $controller->handler,
                );
            }

            return $this->match($methodReflection, $arguments);
        }

        return $this->match(new ReflectionFunction($controller->handler), $arguments);
    }

    protected function match(ReflectionFunctionAbstract $reflection, array $arguments = []): array
    {
        $params = $reflection->getParameters();

        if (empty($params)) {
            return [];
        }

        $result = [];

        foreach ($params as $param) {
            $className = $param->getType()->getName();
            $result[$param->getName()] = $arguments[$className]
                ?? throw new InvalidArgumentException('Contract not found for ' . $className);
        }

        return $result;
    }
}
