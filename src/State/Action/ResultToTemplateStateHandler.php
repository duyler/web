<?php

declare(strict_types=1);

namespace Duyler\Web\State\Action;

use Duyler\EventBus\Contract\State\MainAfterStateHandlerInterface;
use Duyler\EventBus\Enum\ResultStatus;
use Duyler\EventBus\Formatter\IdFormatter;
use Duyler\EventBus\State\Service\StateMainAfterService;
use Duyler\EventBus\State\StateContext;
use Duyler\TwigWrapper\TwigWrapper;
use Duyler\Web\ViewCollection;
use Override;

class ResultToTemplateStateHandler implements MainAfterStateHandlerInterface
{
    public function __construct(
        private ViewCollection $viewCollection,
        private TwigWrapper $twigWrapper,
    ) {}

    #[Override]
    public function handle(StateMainAfterService $stateService, StateContext $context): void
    {
        $actionId = IdFormatter::toString($stateService->getActionId());

        $result = $stateService->getResult($actionId);

        if (ResultStatus::Success === $result->status) {
            if ($this->viewCollection->has($actionId)) {
                $actionView = $this->viewCollection->get($actionId);

                if (null !== $actionView->dataKey) {
                    $this->twigWrapper->content([$actionView->dataKey => $result->data]);
                }
            }
        }
    }

    #[Override]
    public function observed(StateContext $context): array
    {
        return [];
    }
}
