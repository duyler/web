<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\DependencyInjection\ContainerInterface;
use Duyler\ActionBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\ActionBus\Dto\Event;
use Duyler\ActionBus\Enum\ResultStatus;
use Duyler\ActionBus\State\Service\StateMainAfterService;
use Duyler\ActionBus\State\StateContext;
use Duyler\Http\Http;
use Duyler\TwigWrapper\TwigWrapper;
use Duyler\Web\AbstractController;
use Duyler\Web\ArgumentBuilder;
use Duyler\Web\Build\Controller;
use HttpSoft\Response\TextResponse;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class RunControllerStateHandler implements MainAfterStateHandlerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private TwigWrapper $twigWrapper,
        private ArgumentBuilder $argumentBuilder,
    ) {}

    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        /** @var Controller $controllerData */
        $controllerData = $context->read('controller');

        if (null === $controllerData) {
            return;
        }

        $argumentsData = [];

        foreach ($context->read('doActions') as $actionId) {
            if (false === $stateService->resultIsExists($actionId)) {
                return;
            }

            $result = $stateService->getResult($actionId);

            if (ResultStatus::Fail === $result->status) {
                throw new RuntimeException('Action result ' . $actionId . ' received with status "Fail"');
            }

            if (null !== $result->data) {
                $action = $stateService->getById($actionId);
                $argumentsData[$action->contract] = $result->data;
            }
        }

        $arguments = $this->argumentBuilder->build($controllerData, $argumentsData);

        $this->container->bind(
            $controllerData->getBind(),
        );

        $this->container->addProviders(
            $controllerData->getProviders(),
        );

        $controller = is_callable($controllerData->handler)
            ? $controllerData->handler
            : $this->container->get($controllerData->handler);

        if ($controller instanceof AbstractController) {
            $controller->setRenderer($this->twigWrapper);
        }

        if ('__invoke' === $controllerData->getMethod()) {
            $response = $controller(...$arguments);
        } else {
            $response = $controller->{$controllerData->getmethod()}(...$arguments);
        }

        if (null === $response) {
            return;
        }

        if (is_string($response)) {
            $response = new TextResponse($response);
        }

        if (false === $response instanceof ResponseInterface) {
            throw new InvalidArgumentException('Response must be instance of "Psr\Http\Message\ResponseInterface"');
        }

        $stateService->dispatchEvent(
            new Event(
                id: Http::CreateResponse,
                data: $response,
            ),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return $context->read('doActions') ?? [];
    }
}
