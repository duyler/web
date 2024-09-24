<?php

declare(strict_types=1);

namespace Duyler\Web\Controller;

use Duyler\EventBus\Dto\Event as EventDto;
use Duyler\EventBus\Dto\Result;
use Duyler\Web\Renderer\RendererInterface;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\RedirectResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use UnitEnum;

abstract class BaseController
{
    private RendererInterface $renderer;
    private BusService $busService;

    // TODO refactor to with RendererInterface
    public function setRenderer(RendererInterface $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function setBusService(BusService $busService): void
    {
        $this->busService = $busService;
    }

    protected function render(string $template, array $data = []): ResponseInterface
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template);
        if (false === $this->renderer->exists($template)) {
            throw new InvalidArgumentException('Template not found: ' . $template);
        }
        $content = $this->renderer->content($data)->render($template);

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

    protected function dispatchEvent(EventDto $event): void
    {
        $this->busService->dispatchEvent($event);
    }

    protected function getResult(string|UnitEnum $actionId): ?Result
    {
        return $this->busService->getResult($actionId);
    }

    protected function resultIsExists(string|UnitEnum $actionId): bool
    {
        return $this->busService->resultIsExists($actionId);
    }
}
