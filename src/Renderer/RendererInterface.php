<?php

declare(strict_types=1);

namespace Duyler\Web\Renderer;

interface RendererInterface
{
    /**
     * @param array<string, mixed> $variables
     */
    public function content(array $variables): RendererInterface;

    public function render(string $template): string;

    public function exists(string $template): bool;
}
