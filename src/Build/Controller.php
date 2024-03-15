<?php

declare(strict_types=1);

namespace Duyler\Web\Build;

use Closure;
use Duyler\Framework\Build\AttributeInterface;

class Controller
{
    private static ControllerBuilder $builder;
    public readonly string|Closure $handler;
    public readonly string $target;
    private string $method = '__invoke';

    /** @var array<string, string> */
    private array $bind = [];

    /** @var array<string, string> */
    private array $providers = [];

    /** @var string[] */
    private array $contracts = [];

    /** @var AttributeInterface[] */
    private array $attributes = [];

    public function __construct(ControllerBuilder $builder)
    {
        static::$builder = $builder;
    }

    public static function build(string|Closure $handler, ?string $method = null): static
    {
        $method = $method ?? '__invoke';
        $controller = new self(static::$builder);
        $controller->handler = $handler;
        $controller->target = is_object($handler) ? spl_object_hash($handler) : $handler . '@' . $method;
        $controller->method = $method;
        static::$builder->addController($controller);

        return $controller;
    }

    public function contracts(array $contracts): self
    {
        $this->contracts = $contracts;

        return $this;
    }

    public function attributes(AttributeInterface ...$attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function bind(array $bind): self
    {
        $this->bind = $bind;

        return $this;
    }

    public function providers(array $providers): self
    {
        $this->providers = $providers;

        return $this;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getBind(): array
    {
        return $this->bind;
    }

    public function getContracts(): array
    {
        return $this->contracts;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
