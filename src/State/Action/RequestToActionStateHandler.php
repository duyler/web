<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\Router\CurrentRoute;
use InvalidArgumentException;
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

        if (empty($currentRoute->action)) {
            return;
        }

        if ($stateService->actionIsExists($currentRoute->action) === false) {
            throw new InvalidArgumentException('Invalid action: ' . $actionId);
        }

        $stateService->doExistsAction($currentRoute->action);
        $context->write('actionId', $currentRoute->action);
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['Http.StartRouting'];
    }
}
