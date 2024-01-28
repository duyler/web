<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\ResponseTransmitter;
use HttpSoft\Response\JsonResponse;
use Override;
use Psr\Http\Message\ResponseInterface;

class ResultToResponseStateHandler implements MainAfterStateHandlerInterface
{
    public function __construct(
        private ResponseTransmitter $responseTransmitter,
    ) {}

    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $actionId = $context->read('actionId');

        if ($stateService->resultIsExists($actionId)) {
            $responseData = $stateService->getResult($actionId)->data;

            if (is_a($responseData, ResponseInterface::class)) {
                $this->responseTransmitter->transmit($responseData);
            } else {
                $this->responseTransmitter->transmit(new JsonResponse($responseData));
            }
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        if (null === $context->read('actionId')) {
            return [];
        }

        return [$context->read('actionId')];
    }
}
