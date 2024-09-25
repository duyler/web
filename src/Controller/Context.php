<?php

declare(strict_types=1);

namespace Duyler\Web\Controller;

use Closure;
use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Dto\Event;
use Duyler\EventBus\Dto\Result;
use Duyler\Web\Renderer\RendererInterface;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\RedirectResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionFunction;
use ReflectionNamedType;
use UnitEnum;

final readonly class Context
{
    public function __construct(
        private RendererInterface $renderer,
        private BusService $busService,
        private ContainerInterface $container,
        /** @var array<string, object> */
        private array $context = [],
    ) {}

    public function contract(string $contract): ?object
    {
        return $this->context[$contract] ?? null;
    }

    public function render(string $template, array $data = []): ResponseInterface
    {
        $template = str_replace('.', DIRECTORY_SEPARATOR, $template);
        if (false === $this->renderer->exists($template)) {
            throw new InvalidArgumentException('Template not found: ' . $template);
        }
        $content = $this->renderer->content($data)->render($template);

        return new HtmlResponse($content);
    }

    public function call(Closure $callback): mixed
    {
        $reflection = new ReflectionFunction($callback);

        $params = $reflection->getParameters();

        $arguments = [];

        foreach ($params as $param) {
            /** @var ReflectionNamedType|null $paramType */
            $paramType = $param->getType();

            if (null === $paramType) {
                throw new InvalidArgumentException('Type hint not set for ' . $param->getName());
            }

            $className = $paramType->getName();

            $arguments[$param->getName()] = $this->container->get($className);
        }

        return $callback(...$arguments);
    }

    public function json(mixed $data, int $code = 200, array $headers = []): ResponseInterface
    {
        return new JsonResponse($data, $code, $headers);
    }

    public function redirect(
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

    public function dispatchEvent(Event $event): void
    {
        $this->busService->dispatchEvent($event);
    }

    public function getResult(string|UnitEnum $actionId): ?Result
    {
        return $this->busService->getResult($actionId);
    }

    public function resultIsExists(string|UnitEnum $actionId): bool
    {
        return $this->busService->resultIsExists($actionId);
    }
}
