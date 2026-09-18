<?php
declare(strict_types=1);

namespace NewBGP\PestPsr7\Support;

use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\Assert;

final class Response
{
    public static function from(mixed $value): ResponseInterface
    {
        Assert::assertInstanceOf(ResponseInterface::class, $value);
        return $value;
    }

    public static function body(ResponseInterface $response): string
    {
        $stream = $response->getBody();
        Assert::assertTrue($stream->isReadable(), 'Response stream must be readable.');
        Assert::assertTrue($stream->isSeekable(), 'Use a buffered/seekable stream for repeatable assertions.');
        $position = $stream->tell();
        try {
            $stream->rewind();
            return $stream->getContents();
        } finally {
            $stream->seek($position);
        }
    }

    public static function json(ResponseInterface $response): mixed
    {
        try {
            return json_decode(self::body($response), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            Assert::fail('Invalid response JSON: ' . $exception->getMessage());
        }
    }

    public static function mediaType(ResponseInterface $response): string
    {
        return strtolower(trim(explode(';', $response->getHeaderLine('Content-Type'), 2)[0]));
    }
}
