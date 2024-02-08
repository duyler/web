<?php

declare(strict_types=1);

namespace Duyler\Web;

use Closure;
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
    public function build(Closure|string $handler, array $arguments = []): array
    {
        if (is_string($handler)) {
            $reflection = new ReflectionClass($handler);
            $invoke = null;
            foreach ($reflection->getMethods() as $method) {
                if ($method->getName() === '__invoke') {
                    $invoke = $method;
                    break;
                }
            }

            return $this->match($invoke, $arguments);
        }

        return $this->match(new ReflectionFunction($handler), $arguments);
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
