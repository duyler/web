<?php

declare(strict_types=1);

namespace Duyler\Web\Renderer;

use Duyler\TwigWrapper\TwigWrapper;

final class TwigRenderer implements RendererInterface
{
    public function __construct(
        private TwigWrapper $twigWrapper,
    ) {}

    public function content(array $variables): RendererInterface
    {
        $this->twigWrapper->content($variables);
        return $this;
    }

    public function render(string $template): string
    {
        return $this->twigWrapper->render($template);
    }

    public function exists(string $template): bool
    {
        return $this->twigWrapper->exists($template);
    }
}
