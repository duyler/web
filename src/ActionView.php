<?php

declare(strict_types=1);

namespace Duyler\Web;

readonly class ActionView
{
    public function __construct(
        public string $actionId,
        public ?string $viewName,
        public ?string $dataKey,
    ) {}
}
