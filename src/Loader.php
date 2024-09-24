<?php

declare(strict_types=1);

namespace Duyler\Web;

use Duyler\DependencyInjection\ContainerInterface;
use Duyler\EventBus\Build\Context;
use Duyler\Builder\Loader\LoaderServiceInterface;
use Duyler\Builder\Loader\PackageLoaderInterface;
use Duyler\EventBus\Contract\State\StateHandlerInterface;
use Duyler\Web\Build\Attribute\AttributeHandler;
use Duyler\Web\Build\Controller;
use Duyler\Web\Build\ControllerBuilder;
use Duyler\Web\Renderer\RendererInterface;
use Duyler\Web\Renderer\TwigRenderer;
use Duyler\Web\State\Action\RequestToActionStateHandler;
use Duyler\Web\State\Action\ResultToResponseStateHandler;
use Duyler\Web\State\Action\ResultToTemplateStateHandler;
use Duyler\Web\State\Controller\PrepareControllerContractsStateHandler;
use Duyler\Web\State\Controller\RunControllerStateHandler;
use Duyler\Web\State\ResolveRouteStateHandler;
use Override;

class Loader implements PackageLoaderInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {
        $this->container->bind(
            [
                RendererInterface::class => TwigRenderer::class,
            ],
        );
    }

    #[Override]
    public function load(LoaderServiceInterface $loaderService): void
    {
        /** @var ControllerBuilder $controllerBuilder */
        $controllerBuilder = $this->container->get(ControllerBuilder::class);

        /** @var StateHandlerInterface $prepareController */
        $prepareController = $this->container->get(PrepareControllerContractsStateHandler::class);

        /** @var StateHandlerInterface $runController */
        $runController = $this->container->get(RunControllerStateHandler::class);

        /** @var StateHandlerInterface $requestToAction */
        $requestToAction = $this->container->get(RequestToActionStateHandler::class);

        /** @var StateHandlerInterface $resultToResponse */
        $resultToResponse = $this->container->get(ResultToResponseStateHandler::class);

        /** @var StateHandlerInterface $resolveRoute */
        $resolveRoute = $this->container->get(ResolveRouteStateHandler::class);

        /** @var StateHandlerInterface $resultToTemplate */
        $resultToTemplate = $this->container->get(ResultToTemplateStateHandler::class);

        /** @var AttributeHandler $attributeHandler */
        $attributeHandler = $this->container->get(AttributeHandler::class);

        new Controller($controllerBuilder);

        $context = new Context(
            [
                PrepareControllerContractsStateHandler::class,
                RunControllerStateHandler::class,
                RequestToActionStateHandler::class,
                ResultToResponseStateHandler::class,
                ResultToTemplateStateHandler::class,
            ],
        );

        $loaderService->addBuilder($controllerBuilder);
        $loaderService->addAttributeHandler($attributeHandler);
        $loaderService->addStateContext($context);

        $loaderService->addStateHandler($resultToTemplate);
        $loaderService->addStateHandler($prepareController);
        $loaderService->addStateHandler($runController);
        $loaderService->addStateHandler($requestToAction);
        $loaderService->addStateHandler($resultToResponse);
        $loaderService->addStateHandler($resolveRoute);
    }
}
