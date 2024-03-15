<?php

declare(strict_types=1);

namespace Duyler\Web;

use Duyler\TwigWrapper\TwigWrapper;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\RedirectResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractController
{
    private TwigWrapper $twigWrapper;

    // TODO refactor to with RendererInterface
    public function setRenderer(TwigWrapper $twigWrapper): void
    {
        $this->twigWrapper = $twigWrapper;
    }

    protected function render(string $template, array $data = []): ResponseInterface
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template);
        if (false === $this->twigWrapper->exists($template)) {
            throw new InvalidArgumentException('Template not found: ' . $template);
        }
        $content = $this->twigWrapper->content($data)->render($template);

        return new HtmlResponse($content);
    }

    protected function json(mixed $data, int $code = 200, array $headers = []): ResponseInterface
    {
        return new JsonResponse($data, $code, $headers);
    }

    protected function redirect(
        string $uri,
        int $code = 302,
        array $headers = [],
        string $protocol = '1.1',
        string $reasonPhrase = '',
    ): ResponseInterface {
        return new RedirectResponse(
            $uri,
            $code,
            $headers,
            $protocol,
            $reasonPhrase,
        );
    }
}
