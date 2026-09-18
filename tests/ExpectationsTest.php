<?php
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\NoSeekStream;
use GuzzleHttp\Psr7\Utils;
use NewBGP\PestPsr7\Support\Response as ResponseHelper;
use PHPUnit\Framework\AssertionFailedError;

it('checks status categories', function (int $status, string $method) {
    expect(new Response($status))->{$method}()->toHaveStatus($status)->toHaveStatusIn([$status]);
})->with([[100, 'toBeInformational'], [200, 'toBeSuccessful'], [302, 'toBeRedirect'], [422, 'toBeClientError'], [500, 'toBeServerError']]);

it('checks headers and body', function () {
    expect(new Response(200, ['Content-Type' => 'text/plain; charset=utf-8'], 'hello'))
        ->toHaveHeader('content-type', 'text/plain')
        ->toHaveHeaderValue('Content-Type', 'text/plain; charset=utf-8')
        ->toHaveContentType('text/plain')->toNotHaveHeader('X-Missing')
        ->toHaveBody('hello')->toContainBody('ell');
    expect(new Response(204))->toHaveEmptyBody();
});

it('checks all JSON assertions', function () {
    $payload = ['data' => [['id' => 1, 'name' => 'Felipe', 'optional' => null]]];
    $r = new Response(200, ['Content-Type' => 'application/json'], json_encode($payload));
    expect($r)->toBeJsonResponse()->toHaveValidJson()->toHaveExactJson($payload)
        ->toHaveJson(['data' => [['id' => 1]]])
        ->toHaveJsonPath('$.data[0].id', 1)
        ->toHaveJsonPath('data.0.optional', null)
        ->toHaveJsonPathContaining('data.0.name', 'Fel')
        ->toHaveJsonKeys('data', 'data.0.id')
        ->toHaveJsonCount(1, 'data');
});

it('checks error messages and redirects', function () {
    $r = new Response(422, [], '{"message":"Invalid email","errors":{"email":"Required"}}');
    expect($r)->toHaveValidationErrors('email')->toHaveErrorMessage('Invalid email')
        ->toContainErrorMessage('email');
    expect(new Response(301, ['Location' => '/new']))->toRedirectTo('/new', 301);
});

it('accepts JSON media type suffixes', function () {
    expect(new Response(400, ['Content-Type' => 'application/problem+json'], '{}'))
        ->toBeJsonResponse();
});

it('preserves stream position', function () {
    $r = new Response(200, [], 'abcdef');
    $r->getBody()->seek(3);
    expect($r)->toHaveBody('abcdef')->toHaveBody('abcdef');
    expect($r->getBody()->tell())->toBe(3);
});

it('rejects nonseekable streams explicitly', function () {
    $r = new Response(200, [], new NoSeekStream(Utils::streamFor('abc')));
    expect(fn () => ResponseHelper::body($r))->toThrow(AssertionFailedError::class);
});

it('rejects missing paths even when expected value is null', function () {
    expect(new Response(200, [], '{}'))->toHaveJsonPath('missing', null);
})->throws(AssertionFailedError::class);

it('rejects invalid JSON', function () {
    expect(new Response(200, [], 'not json'))->toHaveValidJson();
})->throws(AssertionFailedError::class);

it('rejects missing empty-valued headers', function () {
    expect(new Response())->toHaveHeaderValue('Missing', '');
})->throws(AssertionFailedError::class);

it('rejects a mismatched status', function () {
    expect(new Response(500))->toHaveStatus(200);
})->throws(AssertionFailedError::class);

it('supports negation of simple expectations', function () {
    expect(new Response(201))->not->toHaveStatus(200);
});
