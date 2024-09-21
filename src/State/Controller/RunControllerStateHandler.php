<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Contract\State\MainEmptyStateHandlerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\Http;
use Duyler\TwigWrapper\TwigWrapper;
use Duyler\Web\BaseController;
use Duyler\Web\ArgumentBuilder;
use Duyler\Web\Build\Controller;
use Duyler\Web\BusService;
use Duyler\Web\Context;
use HttpSoft\Response\TextResponse;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class RunControllerStateHandler implements MainEmptyStateHandlerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private TwigWrapper $twigWrapper,
        private ArgumentBuilder $argumentBuilder,
    ) {}

    #[Override]
    public function handle(StateMainEmptyService $stateService, StateContext $context): void
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

        $this->container->bind(
            $controllerData->getBind(),
        );

        $this->container->addProviders(
            $controllerData->getProviders(),
        );

        if (is_callable($controllerData->handler)) {
            $response = ($controllerData->handler)(
                new Context(
                    $this->twigWrapper,
                    new BusService($stateService),
                    $this->container,
                    $argumentsData,
                )
            );
        } else {
            $arguments = $this->argumentBuilder->build($controllerData, $argumentsData);

            $controller = $this->container->get($controllerData->handler);

            if ($controller instanceof BaseController) {
                $controller->setRenderer($this->twigWrapper);
                $controller->setBusService(new BusService($stateService));
            }

            if ('__invoke' === $controllerData->getMethod()) {
                $response = $controller(...$arguments);
            } else {
                $response = $controller->{$controllerData->getMethod()}(...$arguments);
            }
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
}
