<?php

declare(strict_types=1);

namespace NewBGP\PestPsr7;

use Psr\Http\Message\ResponseInterface;

function expectResponse(ResponseInterface $response): Psr7Expectation
{
    return new Psr7Expectation($response);
}
