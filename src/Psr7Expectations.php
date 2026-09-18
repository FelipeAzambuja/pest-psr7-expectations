<?php

declare(strict_types=1);

namespace NewBGP\PestPsr7;

use NewBGP\PestPsr7\Support\Response;
use Pest\Expectation;

final class Psr7Expectations
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        $forward = static function (mixed $value, string $method, array $arguments): void {
            $expectation = new Psr7Expectation(Response::from($value));
            $expectation->{$method}(...$arguments);
        };

        expect()->extend('toHaveStatus', function (int $expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveStatus', [$expected]);

            return $this;
        });

        expect()->extend('toHaveStatusIn', function (array $statuses) use ($forward): Expectation {
            $forward($this->value, 'toHaveStatusIn', [$statuses]);

            return $this;
        });

        expect()->extend('toBeInformational', function () use ($forward): Expectation {
            $forward($this->value, 'toBeInformational', []);

            return $this;
        });

        expect()->extend('toBeSuccessful', function () use ($forward): Expectation {
            $forward($this->value, 'toBeSuccessful', []);

            return $this;
        });

        expect()->extend('toBeRedirect', function () use ($forward): Expectation {
            $forward($this->value, 'toBeRedirect', []);

            return $this;
        });

        expect()->extend('toBeClientError', function () use ($forward): Expectation {
            $forward($this->value, 'toBeClientError', []);

            return $this;
        });

        expect()->extend('toBeServerError', function () use ($forward): Expectation {
            $forward($this->value, 'toBeServerError', []);

            return $this;
        });

        expect()->extend('toHaveHeader', function (string $name, ?string $containing = null) use ($forward): Expectation {
            $forward($this->value, 'toHaveHeader', [$name, $containing]);

            return $this;
        });

        expect()->extend('toHaveHeaderValue', function (string $name, string $expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveHeaderValue', [$name, $expected]);

            return $this;
        });

        expect()->extend('toNotHaveHeader', function (string $name) use ($forward): Expectation {
            $forward($this->value, 'toNotHaveHeader', [$name]);

            return $this;
        });

        expect()->extend('toHaveContentType', function (string $expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveContentType', [$expected]);

            return $this;
        });

        expect()->extend('toBeJsonResponse', function () use ($forward): Expectation {
            $forward($this->value, 'toBeJsonResponse', []);

            return $this;
        });

        expect()->extend('toHaveBody', function (string $expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveBody', [$expected]);

            return $this;
        });

        expect()->extend('toContainBody', function (string $expected) use ($forward): Expectation {
            $forward($this->value, 'toContainBody', [$expected]);

            return $this;
        });

        expect()->extend('toHaveEmptyBody', function () use ($forward): Expectation {
            $forward($this->value, 'toHaveEmptyBody', []);

            return $this;
        });

        expect()->extend('toHaveValidJson', function () use ($forward): Expectation {
            $forward($this->value, 'toHaveValidJson', []);

            return $this;
        });

        expect()->extend('toHaveJson', function (array $expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveJson', [$expected]);

            return $this;
        });

        expect()->extend('toHaveExactJson', function (mixed $expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveExactJson', [$expected]);

            return $this;
        });

        expect()->extend('toHaveJsonPath', function (string $path, mixed ...$expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveJsonPath', [$path, ...$expected]);

            return $this;
        });

        expect()->extend('toHaveJsonPathContaining', function (string $path, string $expected) use ($forward): Expectation {
            $forward($this->value, 'toHaveJsonPathContaining', [$path, $expected]);

            return $this;
        });

        expect()->extend('toHaveJsonKeys', function (string ...$paths) use ($forward): Expectation {
            $forward($this->value, 'toHaveJsonKeys', $paths);

            return $this;
        });

        expect()->extend('toHaveJsonCount', function (int $expected, string $path = '$') use ($forward): Expectation {
            $forward($this->value, 'toHaveJsonCount', [$expected, $path]);

            return $this;
        });

        expect()->extend('toHaveValidationErrors', function (string ...$fields) use ($forward): Expectation {
            $forward($this->value, 'toHaveValidationErrors', $fields);

            return $this;
        });

        expect()->extend('toHaveErrorMessage', function (string $expected, string $path = '$.message') use ($forward): Expectation {
            $forward($this->value, 'toHaveErrorMessage', [$expected, $path]);

            return $this;
        });

        expect()->extend('toContainErrorMessage', function (string $expected, string $path = '$.message') use ($forward): Expectation {
            $forward($this->value, 'toContainErrorMessage', [$expected, $path]);

            return $this;
        });

        expect()->extend('toRedirectTo', function (string $location, ?int $status = null) use ($forward): Expectation {
            $forward($this->value, 'toRedirectTo', [$location, $status]);

            return $this;
        });

        self::$registered = true;
    }
}
