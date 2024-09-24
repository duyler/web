<?php

declare(strict_types=1);

namespace Duyler\Web\Controller;

use Duyler\EventBus\Build\Event;
use Duyler\EventBus\Dto\Event as EventDto;
use Duyler\EventBus\Dto\Result;
use Duyler\EventBus\State\Service\StateMainEmptyService;
use UnitEnum;

class BusService
{
    public function __construct(private StateMainEmptyService $stateMainEmptyService) {}

    public function dispatchEvent(EventDto $event): void
    {
        $this->stateMainEmptyService->dispatchEvent($event);
    }

    public function registerEvent(Event $event): void
    {
        $this->stateMainEmptyService->registerEvent($event);
    }

    public function getResult(string|UnitEnum $actionId): ?Result
    {
        return $this->stateMainEmptyService->resultIsExists($actionId)
            ? $this->stateMainEmptyService->getResult($actionId)
            : null;
    }

    public function resultIsExists(string|UnitEnum $actionId): bool
    {
        return $this->stateMainEmptyService->resultIsExists($actionId);
    }
}
