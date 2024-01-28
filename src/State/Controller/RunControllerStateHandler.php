<?php

declare(strict_types=1);

namespace Duyler\Web\State\Controller;

use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Contract\State\MainEndStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainEndService;
use Duyler\EventBus\State\StateContext;
use Duyler\Http\ResponseTransmitter;
use Duyler\TwigWrapper\TwigWrapper;
use Duyler\Web\AbstractController;
use Duyler\Web\Build\Controller;
use HttpSoft\Response\TextResponse;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ResponseInterface;

class RunControllerStateHandler implements MainEndStateHandlerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ResponseTransmitter $responseTransmitter,
        private TwigWrapper $twigWrapper,
    ) {}

    #[Override]
    public function handle(StateMainEndService $stateService, StateContext $context): void
    {
        /** @var Controller $controllerData */
        $controllerData = $context->read('controller');

        if ($controllerData === null) {
            return;
        }

        $container = clone $this->container;

        $container->bind(
            $controllerData->getBind(),
        );

        $container->addProviders(
            $controllerData->getProviders(),
        );

        $actions = $context->read('doActions');

        foreach ($actions as $contract => $actionId) {
            if ($stateService->resultIsExists($actionId)) {
                $result = $stateService->getResult($actionId);
                if ($result->data !== null) {
                    $container->bind([$contract => $actionId]);
                    $container->set($result->data);
                }
            }
        }

        if (is_callable($controllerData->handler)) {
            $controller = $controllerData->handler;
        } else {
            $controller = $container->get($controllerData->handler);
        }

        if (is_a($controller, AbstractController::class)) {
            $controller->setRenderer($this->twigWrapper);
        }

        $response = $controller();

        if ($response === null) {
            return;
        }

        if (is_string($response)) {
            $response = new TextResponse($response);
        }

        if (!is_a($response, ResponseInterface::class)) {
            throw new InvalidArgumentException('Response must be instance of "Psr\Http\Message\ResponseInterface"');
        }

        $this->responseTransmitter->transmit($response);
    }
}
