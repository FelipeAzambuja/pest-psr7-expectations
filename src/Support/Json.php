<?php
declare(strict_types=1);

namespace NewBGP\PestPsr7\Support;

use PHPUnit\Framework\Assert;

final class Json
{
    /** Simple dot paths and numeric indexes only; not a full JSONPath engine. */
    public static function path(mixed $json, string $path): mixed
    {
        $original = $path;
        $path = preg_replace('/^\$\.?/', '', $path) ?? $path;
        $path = preg_replace('/\[(\d+)\]/', '.$1', $path) ?? $path;
        $path = ltrim($path, '.');
        if ($path === '') {
            return $json;
        }
        foreach (explode('.', $path) as $segment) {
            Assert::assertTrue(
                is_array($json) && array_key_exists($segment, $json),
                sprintf('JSON path "%s" is missing.', $original)
            );
            $json = $json[$segment];
        }
        return $json;
    }

    /** Recursive subset, strict scalar comparisons, numeric indexes preserved. */
    public static function subset(array $expected, mixed $actual): void
    {
        Assert::assertIsArray($actual);
        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual);
            if (is_array($value)) {
                self::subset($value, $actual[$key]);
            } else {
                Assert::assertSame($value, $actual[$key]);
            }
        }
    }
}
