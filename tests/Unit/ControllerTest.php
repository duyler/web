<?php

declare(strict_types=1);

namespace Duyler\Web\Test\Unit;

use Duyler\Web\Build\Controller;
use Duyler\Web\Build\ControllerBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    #[Test]
    public function build_with_method()
    {
        new Controller(
            $this->createMock(ControllerBuilder::class),
        );

        $className = 'ClassName';
        $method = 'method';
        $controller = Controller::build($className, $method);

        $this->assertEquals($className, $controller->handler);
        $this->assertEquals($method, $controller->getMethod());
        $this->assertEquals($className . '@' . $method, $controller->target);
    }

    #[Test]
    public function build_with_clojure_handler()
    {
        new Controller(
            $this->createMock(ControllerBuilder::class),
        );

        $handler = fn() => '';
        $controller = Controller::build($handler);

        $this->assertEquals($handler, $controller->handler);
        $this->assertEquals('__invoke', $controller->getMethod());
        $this->assertEquals(spl_object_hash($handler), $controller->target);
    }
}
