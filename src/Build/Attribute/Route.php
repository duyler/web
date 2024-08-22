<?php

declare(strict_types=1);

namespace Duyler\Web\Build\Attribute;

use Closure;
use Duyler\Builder\Build\AttributeHandlerInterface;
use Duyler\Builder\Build\AttributeInterface;
use Duyler\Web\Enum\Method;
use Override;

readonly class Route implements AttributeInterface
{
    public function __construct(
        public Method $method,
        public string $pattern,
        public ?string $name = null,
        public string|Closure|null $handler = null,
        public ?string $target = null,
        public array $where = [],
    ) {}

    #[Override]
    public function accept(AttributeHandlerInterface $handler, mixed $item): void
    {
        /* @var AttributeHandler $handler */
        $handler->handleRoute($this, $item);
    }
}
