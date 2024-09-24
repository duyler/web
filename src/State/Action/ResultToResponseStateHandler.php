<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainEmptyStateHandlerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\Event\Response;
use Duyler\Web\Renderer\RendererInterface;
use Duyler\Web\ViewCollection;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use InvalidArgumentException;
use LogicException;
use Override;
use Psr\Http\Message\ResponseInterface;

class ResultToResponseStateHandler implements MainEmptyStateHandlerInterface
{
    public function __construct(
        private ViewCollection $viewCollection,
        private RendererInterface $renderer,
    ) {}

    #[Override]
    public function handle(StateMainEmptyService $stateService, StateContext $context): void
    {
        $actionId = $context->read('actionId');

        if (null === $actionId) {
            return;
        }

        if (false === $stateService->resultIsExists($actionId)) {
            throw new LogicException('Result is not exists for action ' . $actionId);
        }

        $responseData = $stateService->getResult($actionId)->data;

        if ($responseData instanceof ResponseInterface) {
            $stateService->dispatchEvent(
                new Event(
                    id: Response::ResponseCreated,
                    data: $responseData,
                ),
            );
        } elseif ($this->viewCollection->has($actionId)) {
            $actionView = $this->viewCollection->get($actionId);

            $template = str_replace('.', DIRECTORY_SEPARATOR, $actionView->viewName);
            if (false === $this->renderer->exists($template)) {
                throw new InvalidArgumentException("Template {$template} not found");
            }

            $content = $this->renderer->render($template);
            $stateService->dispatchEvent(
                new Event(
                    id: Response::ResponseCreated,
                    data: new HtmlResponse($content),
                ),
            );
        } else {
            $stateService->dispatchEvent(
                new Event(
                    id: Response::ResponseCreated,
                    data: new JsonResponse($responseData),
                ),
            );
        }
    }
}
