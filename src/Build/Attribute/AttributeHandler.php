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
        $definition->handler($route->handler ?? $item->handler ?? null);
        $definition->name($route->name);
        $definition->target($route->target ?? $item->target ?? $item::class);
        $definition->action($item->id ?? $route->action ?? '');
        $definition->where($route->where);
    }

    public function handleView(View $view, mixed $item): void
    {
        if ($item instanceof Action === false) {
            throw new InvalidArgumentException(
                'Target item for attribute "View" must be type of ' . Action::class . ', ' . $item::class . ' given',
            );
        }

        $this->viewCollection->add(new ActionView(
            actionId: $item->id,
            viewName: $view->name,
            dataKey: $view->key
        ));
    }
}
