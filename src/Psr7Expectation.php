<?php

declare(strict_types=1);

namespace NewBGP\PestPsr7;

use NewBGP\PestPsr7\Support\Json;
use NewBGP\PestPsr7\Support\Response;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

final class Psr7Expectation
{
    public function __construct(private readonly ResponseInterface $response)
    {
    }

    public function toHaveStatus(int $expected): self
    {
        Assert::assertSame($expected, $this->response->getStatusCode());

        return $this;
    }

    /**
     * @param list<int> $statuses
     */
    public function toHaveStatusIn(array $statuses): self
    {
        Assert::assertTrue(
            in_array($this->response->getStatusCode(), $statuses, true),
            'Unexpected HTTP status.'
        );

        return $this;
    }

    public function toBeInformational(): self
    {
        Assert::assertGreaterThanOrEqual(100, $this->response->getStatusCode());
        Assert::assertLessThanOrEqual(199, $this->response->getStatusCode());

        return $this;
    }

    public function toBeSuccessful(): self
    {
        Assert::assertGreaterThanOrEqual(200, $this->response->getStatusCode());
        Assert::assertLessThanOrEqual(299, $this->response->getStatusCode());

        return $this;
    }

    public function toBeRedirect(): self
    {
        Assert::assertGreaterThanOrEqual(300, $this->response->getStatusCode());
        Assert::assertLessThanOrEqual(399, $this->response->getStatusCode());

        return $this;
    }

    public function toBeClientError(): self
    {
        Assert::assertGreaterThanOrEqual(400, $this->response->getStatusCode());
        Assert::assertLessThanOrEqual(499, $this->response->getStatusCode());

        return $this;
    }

    public function toBeServerError(): self
    {
        Assert::assertGreaterThanOrEqual(500, $this->response->getStatusCode());
        Assert::assertLessThanOrEqual(599, $this->response->getStatusCode());

        return $this;
    }

    public function toHaveHeader(string $name, ?string $containing = null): self
    {
        Assert::assertTrue($this->response->hasHeader($name), 'Missing header: ' . $name);

        if ($containing !== null) {
            Assert::assertStringContainsString($containing, $this->response->getHeaderLine($name));
        }

        return $this;
    }

    public function toHaveHeaderValue(string $name, string $expected): self
    {
        Assert::assertTrue($this->response->hasHeader($name), 'Missing header: ' . $name);
        Assert::assertSame($expected, $this->response->getHeaderLine($name));

        return $this;
    }

    public function toNotHaveHeader(string $name): self
    {
        Assert::assertFalse($this->response->hasHeader($name));

        return $this;
    }

    public function toHaveContentType(string $expected): self
    {
        Assert::assertSame(strtolower(trim($expected)), Response::mediaType($this->response));

        return $this;
    }

    public function toBeJsonResponse(): self
    {
        $type = Response::mediaType($this->response);

        Assert::assertTrue(
            $type === 'application/json'
                || (str_starts_with($type, 'application/') && str_ends_with($type, '+json')),
            'Expected JSON content type.'
        );
        Response::json($this->response);

        return $this;
    }

    public function toHaveBody(string $expected): self
    {
        Assert::assertSame($expected, Response::body($this->response));

        return $this;
    }

    public function toContainBody(string $expected): self
    {
        Assert::assertStringContainsString($expected, Response::body($this->response));

        return $this;
    }

    public function toHaveEmptyBody(): self
    {
        Assert::assertSame('', Response::body($this->response));

        return $this;
    }

    public function toHaveValidJson(): self
    {
        Response::json($this->response);

        return $this;
    }

    /**
     * @param array<array-key, mixed> $expected
     */
    public function toHaveJson(array $expected): self
    {
        Json::subset($expected, Response::json($this->response));

        return $this;
    }

    public function toHaveExactJson(mixed $expected): self
    {
        Assert::assertSame($expected, Response::json($this->response));

        return $this;
    }

    public function toHaveJsonPath(string $path, mixed ...$expected): self
    {
        Assert::assertLessThanOrEqual(1, count($expected), 'Pass at most one expected value.');
        $actual = Json::path(Response::json($this->response), $path);

        if ($expected !== []) {
            Assert::assertSame($expected[0], $actual);
        }

        return $this;
    }

    public function toHaveJsonPathContaining(string $path, string $expected): self
    {
        $actual = Json::path(Response::json($this->response), $path);

        Assert::assertIsString($actual);
        Assert::assertStringContainsString($expected, $actual);

        return $this;
    }

    public function toHaveJsonKeys(string ...$paths): self
    {
        $json = Response::json($this->response);

        foreach ($paths as $path) {
            Json::path($json, $path);
        }

        return $this;
    }

    public function toHaveJsonCount(int $expected, string $path = '$'): self
    {
        $value = Json::path(Response::json($this->response), $path);

        Assert::assertIsArray($value);
        Assert::assertCount($expected, $value);

        return $this;
    }

    public function toHaveValidationErrors(string ...$fields): self
    {
        $errors = Json::path(Response::json($this->response), 'errors');

        Assert::assertIsArray($errors);
        Assert::assertNotEmpty($errors);

        foreach ($fields as $field) {
            Assert::assertArrayHasKey($field, $errors);
        }

        return $this;
    }

    public function toHaveErrorMessage(string $expected, string $path = '$.message'): self
    {
        Assert::assertSame($expected, Json::path(Response::json($this->response), $path));

        return $this;
    }

    public function toContainErrorMessage(string $expected, string $path = '$.message'): self
    {
        $actual = Json::path(Response::json($this->response), $path);

        Assert::assertIsString($actual);
        Assert::assertStringContainsString($expected, $actual);

        return $this;
    }

    public function toRedirectTo(string $location, ?int $status = null): self
    {
        Assert::assertContains($this->response->getStatusCode(), [301, 302, 303, 307, 308]);

        if ($status !== null) {
            Assert::assertSame($status, $this->response->getStatusCode());
        }

        Assert::assertTrue($this->response->hasHeader('Location'));
        Assert::assertSame($location, $this->response->getHeaderLine('Location'));

        return $this;
    }
}
