<?php

declare(strict_types=1);

namespace integration;

use Duyler\Web\Build\Controller;
use Duyler\Web\Build\ControllerBuilder;
use Duyler\Web\ControllerCollection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BuildControllerTest extends TestCase
{
    #[Test]
    public function build()
    {
        $controllerCollection = new ControllerCollection();
        $controllerBuilder = new ControllerBuilder($controllerCollection);
        new Controller($controllerBuilder);

        Controller::build('ClassName', 'method');

        $this->assertCount(1, $controllerCollection->getAll());

        $this->expectException(InvalidArgumentException::class);

        Controller::build('ClassName', 'method');
    }
}
