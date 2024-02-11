<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\TwigWrapper\TwigWrapper;
use Duyler\Web\ViewCollection;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use InvalidArgumentException;
use LogicException;
use Override;
use Psr\Http\Message\ResponseInterface;

class ResultToResponseStateHandler implements MainAfterStateHandlerInterface
{
    public function __construct(
        private ViewCollection $viewCollection,
        private TwigWrapper $twigWrapper,
    ) {}

    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $actionId = $context->read('actionId');

        if ($stateService->resultIsExists($actionId) === false) {
            throw new LogicException('Result is not exists for action ' . $actionId);
        }

        $responseData = $stateService->getResult($actionId)->data;

        if ($responseData instanceof ResponseInterface) {
            $stateService->doTrigger(
                new Trigger(
                    id: 'Http.CreateResponse',
                    status: ResultStatus::Success,
                    data: $responseData,
                    contract: ResponseInterface::class,
                ),
            );
        } elseif ($this->viewCollection->has($actionId)) {
            $actionView = $this->viewCollection->get($actionId);

            $template = str_replace('.', DIRECTORY_SEPARATOR, $actionView->viewName);
            if ($this->twigWrapper->exists($template) === false) {
                throw new InvalidArgumentException("Template {$template} not found");
            }

            $this->twigWrapper->content([$actionView->dataKey => $responseData]);
            $content = $this->twigWrapper->render($template);
            $stateService->doTrigger(
                new Trigger(
                    id: 'Http.CreateResponse',
                    status: ResultStatus::Success,
                    data: new HtmlResponse($content),
                    contract: ResponseInterface::class,
                ),
            );
        } else {
            $stateService->doTrigger(
                new Trigger(
                    id: 'Http.CreateResponse',
                    status: ResultStatus::Success,
                    data: new JsonResponse($responseData),
                    contract: ResponseInterface::class,
                ),
            );
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [$context->read('actionId')];
    }
}
