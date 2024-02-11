<?php

declare(strict_types=1);

namespace Duyler\Web;

class ViewCollection
{
    /** @var array<string, ActionView>  */
    private array $views = [];

    public function add(ActionView $actionView): void
    {
        $this->views[$actionView->actionId] = $actionView;
    }

    public function get(string $actionId): ActionView|null
    {
        return $this->views[$actionId] ?? null;
    }

    public function has(string $actionId): bool
    {
        return isset($this->views[$actionId]);
    }
}
