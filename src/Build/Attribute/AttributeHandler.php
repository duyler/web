<?php

declare(strict_types=1);

namespace Duyler\Web\Build\Attribute;

use Duyler\EventBus\Dto\Action;
use Duyler\Framework\Build\AttributeHandlerInterface;
use Duyler\Router\Route;
use Duyler\Router\RouteDefinition;
use Duyler\Web\ActionView;
use Duyler\Web\Build\Attribute\Route as RouteAttribute;
use Duyler\Web\ViewCollection;
use InvalidArgumentException;
use Override;

class AttributeHandler implements AttributeHandlerInterface
{
    public function __construct(
        private ViewCollection $viewCollection,
    ) {}

    #[Override]
    public function getAttributeClasses(): array
    {
        return [
            RouteAttribute::class,
            View::class,
        ];
    }

    public function handleRoute(RouteAttribute $route, mixed $item): void
    {
        $method = strtolower($route->method->value);
        /** @var RouteDefinition $definition */
        $definition = Route::{$method}($route->pattern);

        if (null !== $item?->handler) {
            $definition->handler($route->handler);
        }

        if (null !== $route->name) {
            $definition->name($route->name);
        }

        $definition->where($route->where);

        $definition->target(
            $item?->target
                ?? $item?->id
                ?? throw new InvalidArgumentException(
                    'Target value for attribute "Route" not set'
                )
        );
    }

    public function handleView(View $view, mixed $item): void
    {
        if (false === $item instanceof Action) {
            throw new InvalidArgumentException(
                'Target item for attribute "View" must be type of ' . Action::class . ', ' . $item::class . ' given'
            );
        }

        $this->viewCollection->add(new ActionView(
            actionId: $item->id,
            viewName: $view->name,
            dataKey: $view->key
        ));
    }
}
