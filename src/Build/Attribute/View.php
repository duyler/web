<?php

declare(strict_types=1);

namespace Duyler\Web\Build\Attribute;

use Duyler\Framework\Build\AttributeHandlerInterface;
use Duyler\Framework\Build\AttributeInterface;
use Override;

readonly class View implements AttributeInterface
{
    public function __construct(
        public string $name,
        public ?string $key = null,
    ) {}

    #[Override]
    public function accept(AttributeHandlerInterface $handler, mixed $item): void
    {
        /** @var AttributeHandler $handler */
        $handler->handleView($this, $item);
    }
}
