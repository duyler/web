<?php

declare(strict_types=1);

namespace Duyler\Web\State;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\Action\Router;
use Duyler\Http\Exception\NotFoundHttpException;
use Duyler\Router\CurrentRoute;
use Override;

class ResolveRouteStateHandler implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        /** @var CurrentRoute $route */
        $route = $stateService->getResultData();

        if (false === $route->status) {
            throw new NotFoundHttpException();
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [Router::GetRoute];
    }
}
