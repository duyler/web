<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\Router\CurrentRoute;
use Duyler\Web\ControllerCollection;
use InvalidArgumentException;
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

        foreach ($controller->getContracts() as $key => $contract) {
            $actions = $stateService->getByContract($contract);

            if (0 === count($actions)) {
                throw new InvalidArgumentException('Action with contract ' . $contract . ' not found in the bus');
            }

            if (count($actions) > 1) {
                if (false === $stateService->actionIsExists((string) $key)) {
                    throw new InvalidArgumentException(
                        'Multiple contract implementations found, but action id for contract ' . $contract . ' not set'
                    );
                }
                $stateService->doExistsAction($key);
                $doActions[$contract] = $key;
                continue;
            }

            $action = array_shift($actions);

            $stateService->doExistsAction($action->id);
            $doActions[$contract] = $action->id;
        }

        $context->write('controller', $controller);
        $context->write('doActions', $doActions);
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['Http.StartRouting'];
    }
}
