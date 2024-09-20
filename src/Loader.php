<?php

declare(strict_types=1);

namespace Duyler\Web;

use Duyler\EventBus\Build\Context;
use Duyler\Builder\Loader\LoaderServiceInterface;
use Duyler\Builder\Loader\PackageLoaderInterface;
use Duyler\Web\Build\Attribute\AttributeHandler;
use Duyler\Web\Build\Controller;
use Duyler\Web\Build\ControllerBuilder;
use Duyler\Web\State\Action\RequestToActionStateHandler;
use Duyler\Web\State\Action\ResultToResponseStateHandler;
use Duyler\Web\State\Action\ResultToTemplateStateHandler;
use Duyler\Web\State\Controller\PrepareControllerContractsStateHandler;
use Duyler\Web\State\Controller\RunControllerStateHandler;
use Duyler\Web\State\ResolveRouteStateHandler;
use Override;
use Psr\Container\ContainerInterface;

class Loader implements PackageLoaderInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    #[Override]
    public function load(LoaderServiceInterface $loaderService): void
    {
        $controllerBuilder = $this->container->get(ControllerBuilder::class);
        $prepareController = $this->container->get(PrepareControllerContractsStateHandler::class);
        $runController = $this->container->get(RunControllerStateHandler::class);
        $requestToAction = $this->container->get(RequestToActionStateHandler::class);
        $resultToResponse = $this->container->get(ResultToResponseStateHandler::class);
        $resolveRoute = $this->container->get(ResolveRouteStateHandler::class);
        $resultToTemplate = $this->container->get(ResultToTemplateStateHandler::class);
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
