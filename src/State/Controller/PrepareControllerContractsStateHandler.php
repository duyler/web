<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\Http;
use Duyler\Router\CurrentRoute;
use Duyler\Web\ControllerCollection;
use Override;

class PrepareControllerContractsStateHandler implements MainAfterStateHandlerInterface
{
    public function __construct(
        private ControllerCollection $controllerCollection,
    ) {}

    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        /** @var CurrentRoute $currentRoute */
        $currentRoute = $stateService->getResultData();

        if (null === $currentRoute->target) {
            return;
        }

        if (false === $this->controllerCollection->has($currentRoute->target)) {
            return;
        }

        $controller = $this->controllerCollection->get($currentRoute->target);

        $doActions = [];

        foreach ($controller->getActions() as $actionId) {
            if ($stateService->actionIsExists($actionId)) {
                $stateService->doExistsAction($actionId);
                $doActions[] = $actionId;
            }
        }

        $context->write('controller', $controller);
        $context->write('doActions', $doActions);
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [Http::GetRoute];
    }
}
