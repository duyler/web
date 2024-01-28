<?php

declare(strict_types=1);

namespace Duyler\Web\Build;

use Duyler\Framework\Build\AttributeHandlerCollection;
use Duyler\Framework\Build\BuilderInterface;
use Duyler\Web\ControllerCollection;
use Override;

class ControllerBuilder implements BuilderInterface
{
    public function __construct(
        private ControllerCollection $controllerCollection,
    ) {}

    public function addController(Controller $controller): void
    {
        $this->controllerCollection->add($controller);
    }

    #[Override]
    public function build(AttributeHandlerCollection $attributeHandlerCollection): void
    {
        foreach ($this->controllerCollection->getAll() as $controller) {
            foreach ($controller->getAttributes() as $attribute) {
                $attributeHandlers = $attributeHandlerCollection->get($attribute::class);
                foreach ($attributeHandlers as $attributeHandler) {
                    $attribute->accept($attributeHandler, $controller);
                }
            }
        }
    }
}
