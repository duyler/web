<?php

declare(strict_types=1);

namespace Duyler\Web\Enum;

enum HttpMethod: string
{
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'DELETE';
    case Options = 'OPTIONS';
    case Head = 'HEAD';
}
