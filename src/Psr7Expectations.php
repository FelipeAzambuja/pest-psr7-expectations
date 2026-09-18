<?php
declare(strict_types=1);

namespace NewBGP\PestPsr7;

use NewBGP\PestPsr7\Support\Json;
use NewBGP\PestPsr7\Support\Response;
use Pest\Expectation;
use PHPUnit\Framework\Assert;

final class Psr7Expectations
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        expect()->extend('toHaveStatus', function (int $expected): Expectation {
            $r = Response::from($this->value);
            Assert::assertSame($expected, $r->getStatusCode());
            return $this;
        });

        expect()->extend('toHaveStatusIn', function (array $statuses): Expectation {
            $r = Response::from($this->value);
            Assert::assertTrue(in_array($r->getStatusCode(), $statuses, true), 'Unexpected HTTP status.');
            return $this;
        });

        expect()->extend('toBeInformational', function (): Expectation {
            $r = Response::from($this->value);
            Assert::assertGreaterThanOrEqual(100, $r->getStatusCode());
            Assert::assertLessThanOrEqual(199, $r->getStatusCode());
            return $this;
        });

        expect()->extend('toBeSuccessful', function (): Expectation {
            $r = Response::from($this->value);
            Assert::assertGreaterThanOrEqual(200, $r->getStatusCode());
            Assert::assertLessThanOrEqual(299, $r->getStatusCode());
            return $this;
        });

        expect()->extend('toBeRedirect', function (): Expectation {
            $r = Response::from($this->value);
            Assert::assertGreaterThanOrEqual(300, $r->getStatusCode());
            Assert::assertLessThanOrEqual(399, $r->getStatusCode());
            return $this;
        });

        expect()->extend('toBeClientError', function (): Expectation {
            $r = Response::from($this->value);
            Assert::assertGreaterThanOrEqual(400, $r->getStatusCode());
            Assert::assertLessThanOrEqual(499, $r->getStatusCode());
            return $this;
        });

        expect()->extend('toBeServerError', function (): Expectation {
            $r = Response::from($this->value);
            Assert::assertGreaterThanOrEqual(500, $r->getStatusCode());
            Assert::assertLessThanOrEqual(599, $r->getStatusCode());
            return $this;
        });

        expect()->extend('toHaveHeader', function (string $name, ?string $containing = null): Expectation {
            $r = Response::from($this->value);
            Assert::assertTrue($r->hasHeader($name), 'Missing header: ' . $name);
            if ($containing !== null) {
                Assert::assertStringContainsString($containing, $r->getHeaderLine($name));
            }
            return $this;
        });

        expect()->extend('toHaveHeaderValue', function (string $name, string $expected): Expectation {
            $r = Response::from($this->value);
            Assert::assertTrue($r->hasHeader($name), 'Missing header: ' . $name);
            Assert::assertSame($expected, $r->getHeaderLine($name));
            return $this;
        });

        expect()->extend('toNotHaveHeader', function (string $name): Expectation {
            $r = Response::from($this->value);
            Assert::assertFalse($r->hasHeader($name));
            return $this;
        });

        expect()->extend('toHaveContentType', function (string $expected): Expectation {
            $r = Response::from($this->value);
            Assert::assertSame(strtolower(trim($expected)), Response::mediaType($r));
            return $this;
        });

        expect()->extend('toBeJsonResponse', function (): Expectation {
            $r = Response::from($this->value);
            $type = Response::mediaType($r);
            Assert::assertTrue($type === 'application/json' || (str_starts_with($type, 'application/') && str_ends_with($type, '+json')), 'Expected JSON content type.');
            Response::json($r);
            return $this;
        });

        expect()->extend('toHaveBody', function (string $expected): Expectation {
            $r = Response::from($this->value);
            Assert::assertSame($expected, Response::body($r));
            return $this;
        });

        expect()->extend('toContainBody', function (string $expected): Expectation {
            $r = Response::from($this->value);
            Assert::assertStringContainsString($expected, Response::body($r));
            return $this;
        });

        expect()->extend('toHaveEmptyBody', function (): Expectation {
            $r = Response::from($this->value);
            Assert::assertSame('', Response::body($r));
            return $this;
        });

        expect()->extend('toHaveValidJson', function (): Expectation {
            $r = Response::from($this->value);
            Response::json($r);
            return $this;
        });

        expect()->extend('toHaveJson', function (array $expected): Expectation {
            $r = Response::from($this->value);
            Json::subset($expected, Response::json($r));
            return $this;
        });

        expect()->extend('toHaveExactJson', function (mixed $expected): Expectation {
            $r = Response::from($this->value);
            Assert::assertSame($expected, Response::json($r));
            return $this;
        });

        expect()->extend('toHaveJsonPath', function (string $path, mixed ...$expected): Expectation {
            $r = Response::from($this->value);
            Assert::assertLessThanOrEqual(1, count($expected), 'Pass at most one expected value.');
            $actual = Json::path(Response::json($r), $path);
            if ($expected !== []) {
                Assert::assertSame($expected[0], $actual);
            }
            return $this;
        });

        expect()->extend('toHaveJsonPathContaining', function (string $path, string $expected): Expectation {
            $r = Response::from($this->value);
            $actual = Json::path(Response::json($r), $path);
            Assert::assertIsString($actual);
            Assert::assertStringContainsString($expected, $actual);
            return $this;
        });

        expect()->extend('toHaveJsonKeys', function (string ...$paths): Expectation {
            $r = Response::from($this->value);
            $json = Response::json($r);
            foreach ($paths as $path) {
                Json::path($json, $path);
            }
            return $this;
        });

        expect()->extend('toHaveJsonCount', function (int $expected, string $path = '$'): Expectation {
            $r = Response::from($this->value);
            $value = Json::path(Response::json($r), $path);
            Assert::assertIsArray($value);
            Assert::assertCount($expected, $value);
            return $this;
        });

        expect()->extend('toHaveValidationErrors', function (string ...$fields): Expectation {
            $r = Response::from($this->value);
            $errors = Json::path(Response::json($r), 'errors');
            Assert::assertIsArray($errors);
            Assert::assertNotEmpty($errors);
            foreach ($fields as $field) {
                Assert::assertArrayHasKey($field, $errors);
            }
            return $this;
        });

        expect()->extend('toHaveErrorMessage', function (string $expected, string $path = '$.message'): Expectation {
            $r = Response::from($this->value);
            Assert::assertSame($expected, Json::path(Response::json($r), $path));
            return $this;
        });

        expect()->extend('toContainErrorMessage', function (string $expected, string $path = '$.message'): Expectation {
            $r = Response::from($this->value);
            $actual = Json::path(Response::json($r), $path);
            Assert::assertIsString($actual);
            Assert::assertStringContainsString($expected, $actual);
            return $this;
        });

        expect()->extend('toRedirectTo', function (string $location, ?int $status = null): Expectation {
            $r = Response::from($this->value);
            Assert::assertContains($r->getStatusCode(), [301, 302, 303, 307, 308]);
            if ($status !== null) {
                Assert::assertSame($status, $r->getStatusCode());
            }
            Assert::assertTrue($r->hasHeader('Location'));
            Assert::assertSame($location, $r->getHeaderLine('Location'));
            return $this;
        });

        self::$registered = true;
    }
}
