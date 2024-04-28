<?php

declare(strict_types=1);

namespace Duyler\Web\State;

use Duyler\ActionBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\ActionBus\State\Service\StateMainAfterService;
use Duyler\ActionBus\State\StateContext;
use Duyler\Http\Exception\NotFoundHttpException;
use Duyler\Http\Http;
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
        return [Http::GetRoute];
    }
}
