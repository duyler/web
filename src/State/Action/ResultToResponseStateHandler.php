<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainEmptyStateHandlerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\Http;
use Duyler\TwigWrapper\TwigWrapper;
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
        private TwigWrapper $twigWrapper,
    ) {}

    #[Override]
    public function handle(StateMainEmptyService $stateService, StateContext $context): void
    {
        $actionId = $context->read('actionId');

        if (false === $stateService->resultIsExists($actionId)) {
            throw new LogicException('Result is not exists for action ' . $actionId);
        }

        $responseData = $stateService->getResult($actionId)->data;

        if ($responseData instanceof ResponseInterface) {
            $stateService->dispatchEvent(
                new Event(
                    id: Http::CreateResponse,
                    data: $responseData,
                ),
            );
        } elseif ($this->viewCollection->has($actionId)) {
            $actionView = $this->viewCollection->get($actionId);

            $template = str_replace('.', DIRECTORY_SEPARATOR, $actionView->viewName);
            if (false === $this->twigWrapper->exists($template)) {
                throw new InvalidArgumentException("Template {$template} not found");
            }

            if (null !== $actionView->dataKey) {
                $this->twigWrapper->content([$actionView->dataKey => $responseData]);
            }

            $content = $this->twigWrapper->render($template);
            $stateService->dispatchEvent(
                new Event(
                    id: Http::CreateResponse,
                    data: new HtmlResponse($content),
                ),
            );
        } else {
            $stateService->dispatchEvent(
                new Event(
                    id: Http::CreateResponse,
                    data: new JsonResponse($responseData),
                ),
            );
        }
    }
}
