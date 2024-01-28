<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ServerRequestInterface;

class RequestToActionStateHandler implements MainAfterStateHandlerInterface
{
    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        /**
         * @var ServerRequestInterface $request
         */
        $request = $stateService->getResultData();

        $actionId = $request->getAttribute('action');

        if (empty($actionId)) {
            return;
        }

        if ($stateService->actionIsExists($actionId) === false) {
            throw new InvalidArgumentException('Invalid action: ' . $actionId);
        }

        $stateService->doExistsAction($actionId);
        $context->write('actionId', $actionId);
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return ['Http.CreateRequest'];
    }
}
