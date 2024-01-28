<?php

declare(strict_types=1);

namespace Duyler\Web\Build\Attribute;

use Duyler\Framework\Build\AttributeHandlerInterface;
use Duyler\Router\Route;
use Duyler\Router\RouteDefinition;
use Duyler\Web\Build\Attribute\Route as RouteAttribute;
use Override;

class AttributeHandler implements AttributeHandlerInterface
{
    #[Override]
    public function getAttributeClasses(): array
    {
        return [
            RouteAttribute::class,
        ];
    }

    public function handleRoute(RouteAttribute $route, mixed $item): void
    {
        $method = strtolower($route->method);
        /** @var RouteDefinition $definition */
        $definition = Route::{$method}($route->pattern);
        $definition->handler($route->handler ?? $item->handler ?? null);
        $definition->name($route->name);
        $definition->target($route->target ?? $item->target ?? $item::class);
        $definition->action($item->id ?? $route->action ?? '');
        $definition->where($route->where);
    }
}
