<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\ActionBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\ActionBus\State\Service\StateMainEndService;
use Duyler\ActionBus\State\StateContext;
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

        foreach ($actions as $actionId) {
            if (false === $stateService->resultIsExists($actionId)) {
                throw new RuntimeException('Action result for' . $actionId . ' not received');
            }
        }
    }
}
