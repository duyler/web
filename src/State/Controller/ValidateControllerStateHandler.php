<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\EventBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\StateContext;
use Duyler\Web\Build\Controller;
use Override;
use RuntimeException;

class ValidateControllerStateHandler implements MainEndStateHandlerInterface
{
    #[Override]
    public function handle(StateMainEndService $stateService, StateContext $context): void
    {
        /** @var Controller $controllerData */
        $controllerData = $context->read('controller');

        if (null === $controllerData) {
            return;
        }

        $actions = $context->read('doActions');

        foreach ($actions as $contract => $actionId) {
            if (false === $stateService->resultIsExists($actionId)) {
                throw new RuntimeException('Contract ' . $contract . ' not received');
            }
        }
    }
}
