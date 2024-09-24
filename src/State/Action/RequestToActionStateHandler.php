<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\Action\Route;
use Duyler\Router\CurrentRoute;
use Override;

class RequestToActionStateHandler implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        /**
         * @var CurrentRoute $currentRoute
         */
        $currentRoute = $stateService->getResultData();

        if (null === $currentRoute->target) {
            return;
        }

        if (false === $stateService->actionIsExists($currentRoute->target)) {
            return;
        }

        $stateService->doExistsAction($currentRoute->target);
        $context->write('actionId', $currentRoute->target);
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [Route::GetRoute];
    }
}
