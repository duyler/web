<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Dto\Trigger;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\TwigWrapper\TwigWrapper;
use Duyler\Web\AbstractController;
use Duyler\Web\ArgumentBuilder;
use Duyler\Web\Build\Controller;
use HttpSoft\Response\TextResponse;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseInterface;

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

        if ($controllerData === null) {
            return;
        }

        $argumentsData = [];

        foreach ($context->read('doActions') as $contract => $actionId) {
            if ($stateService->resultIsExists($actionId) === false) {
                return;
            }

            $result = $stateService->getResult($actionId);

            if ($result->data !== null) {
                $argumentsData[$contract] = $result->data;
            }
        }

        $arguments = $this->argumentBuilder->build($controllerData, $argumentsData);

        $container = clone $this->container;

        $container->bind(
            $controllerData->getBind(),
        );

        $container->addProviders(
            $controllerData->getProviders(),
        );

        $controller = is_callable($controllerData->handler)
            ? $controllerData->handler
            : $container->get($controllerData->handler);

        if ($controller instanceof AbstractController) {
            $controller->setRenderer($this->twigWrapper);
        }

        if ($controllerData->getMethod() === '__invoke') {
            $response = $controller(...$arguments);
        } else {
            $response = $controller->{$controllerData->getmethod()}(...$arguments);
        }

        if ($response === null) {
            return;
        }

        if (is_string($response)) {
            $response = new TextResponse($response);
        }

        if ($response instanceof ResponseInterface === false) {
            throw new InvalidArgumentException('Response must be instance of "Psr\Http\Message\ResponseInterface"');
        }

        $stateService->doTrigger(
            new Trigger(
                id: 'Http.CreateResponse',
                status: ResultStatus::Success,
                data: $response,
                contract: ResponseInterface::class,
            ),
        );
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return $context->read('doActions') ?? [];
    }
}
