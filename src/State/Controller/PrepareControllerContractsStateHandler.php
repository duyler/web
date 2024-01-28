<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\RequestProvider;
use Duyler\Web\ControllerCollection;
use InvalidArgumentException;
use Override;

class PrepareControllerContractsStateHandler implements MainBeginStateHandlerInterface
{
    public function __construct(
        private ControllerCollection $controllerCollection,
        private RequestProvider $requestProvider,
    ) {}

    #[Override]
    public function handle(StateMainBeginService $stateService, StateContext $context): void
    {
        $target = $this->requestProvider->get()->getAttribute('target');

        $controller = $this->controllerCollection->get($target);

        if ($controller === null) {
            return;
        }

        $doActions = [];

        foreach ($controller->getContracts() as $key => $contract) {
            $actions = $stateService->getByContract($contract);

            if (count($actions) === 0) {
                throw new InvalidArgumentException('Action with contract ' . $contract . ' not found in the bus');
            }

            if (count($actions) > 1) {
                if ($stateService->actionIsExists((string) $key) === false) {
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
}
