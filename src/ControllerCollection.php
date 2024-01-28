<?php

declare(strict_types=1);

namespace Duyler\Web;

use Duyler\Web\Build\Controller;

class ControllerCollection
{
    /** @var Controller[] */
    private array $controllers = [];

    public function add(Controller $controller): void
    {
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
