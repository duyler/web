<?php

declare(strict_types=1);

namespace Duyler\Web;

use Duyler\Web\Build\Controller;
use InvalidArgumentException;

class ControllerCollection
{
    /** @var Controller[] */
    private array $controllers = [];

    public function add(Controller $controller): void
    {
        if (isset($this->controllers[$controller->target])) {
            throw new InvalidArgumentException('Definition of controller ' . $controller->target . ' already exists');
        }

        $this->controllers[$controller->target] = $controller;
    }

    public function get(string $target): ?Controller
    {
        return $this->controllers[$target] ?? null;
    }

    public function getAll(): array
    {
        return $this->controllers;
    }
}
