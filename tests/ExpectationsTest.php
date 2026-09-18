<?php
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\NoSeekStream;
use GuzzleHttp\Psr7\Utils;
use NewBGP\PestPsr7\Psr7Expectation;
use NewBGP\PestPsr7\Support\Response as ResponseHelper;
use PHPUnit\Framework\AssertionFailedError;
use function NewBGP\PestPsr7\expectResponse;

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

it('creates a new typed expectation instance', function () {
    $response = new Response(200);

    expect(expectResponse($response))->not->toBe(expectResponse($response));
});

it('provides a typed fluent API for every expectation', function () {
    $payload = [
        'data' => [
            ['id' => 1, 'name' => 'Felipe', 'optional' => null],
        ],
    ];
    $jsonResponse = new Response(200, ['Content-Type' => 'application/json'], json_encode($payload));
    $expectation = expectResponse($jsonResponse);

    expect($expectation)->toBeInstanceOf(Psr7Expectation::class);
    expect($expectation->toHaveStatus(200))->toBe($expectation);
    expect($expectation->toHaveStatusIn([200, 201]))->toBe($expectation);
    $informational = expectResponse(new Response(100));
    expect($informational->toBeInformational())->toBe($informational);
    $successful = expectResponse(new Response(200));
    expect($successful->toBeSuccessful())->toBe($successful);
    $redirect = expectResponse(new Response(302));
    expect($redirect->toBeRedirect())->toBe($redirect);
    $clientError = expectResponse(new Response(422));
    expect($clientError->toBeClientError())->toBe($clientError);
    $serverError = expectResponse(new Response(500));
    expect($serverError->toBeServerError())->toBe($serverError);
    expect($expectation->toHaveHeader('Content-Type', 'application/json'))->toBe($expectation);
    expect($expectation->toHaveHeaderValue('Content-Type', 'application/json'))->toBe($expectation);
    expect($expectation->toNotHaveHeader('X-Missing'))->toBe($expectation);
    expect($expectation->toHaveContentType('APPLICATION/JSON'))->toBe($expectation);
    expect($expectation->toBeJsonResponse())->toBe($expectation);
    expect($expectation->toHaveBody(json_encode($payload)))->toBe($expectation);
    expect($expectation->toContainBody('Felipe'))->toBe($expectation);
    expect($expectation->toHaveValidJson())->toBe($expectation);
    expect($expectation->toHaveJson(['data' => [['id' => 1]]]))->toBe($expectation);
    expect($expectation->toHaveExactJson($payload))->toBe($expectation);
    expect($expectation->toHaveJsonPath('data.0.id', 1))->toBe($expectation);
    expect($expectation->toHaveJsonPathContaining('data.0.name', 'Fel'))->toBe($expectation);
    expect($expectation->toHaveJsonKeys('data', 'data.0.id'))->toBe($expectation);
    expect($expectation->toHaveJsonCount(1, 'data'))->toBe($expectation);
    expect($expectation->toHaveJsonPath('data.0.optional', null))->toBe($expectation);

    $errors = new Response(
        422,
        ['Content-Type' => 'application/json'],
        '{"message":"Invalid email","errors":{"email":"Required"}}'
    );
    $errorExpectation = expectResponse($errors);
    expect($errorExpectation->toHaveValidationErrors('email'))->toBe($errorExpectation);
    expect($errorExpectation->toHaveErrorMessage('Invalid email'))->toBe($errorExpectation);
    expect($errorExpectation->toContainErrorMessage('email'))->toBe($errorExpectation);

    $redirectExpectation = expectResponse(new Response(301, ['Location' => '/new']));
    expect($redirectExpectation->toRedirectTo('/new', 301))->toBe($redirectExpectation);

    $emptyExpectation = expectResponse(new Response(204));
    expect($emptyExpectation->toHaveEmptyBody())->toBe($emptyExpectation);
});

it('reports typed expectation validation failures clearly', function () {
    $invalidCases = [
        fn () => expectResponse(new Response(500))->toHaveStatus(200),
        fn () => expectResponse(new Response(500))->toHaveStatusIn([200]),
        fn () => expectResponse(new Response(200))->toBeInformational(),
        fn () => expectResponse(new Response(500))->toBeSuccessful(),
        fn () => expectResponse(new Response(200))->toBeRedirect(),
        fn () => expectResponse(new Response(200))->toBeClientError(),
        fn () => expectResponse(new Response(200))->toBeServerError(),
        fn () => expectResponse(new Response())->toHaveHeader('X-Missing'),
        fn () => expectResponse(new Response(200, ['X-Test' => 'actual']))->toHaveHeaderValue('X-Test', 'expected'),
        fn () => expectResponse(new Response(200, ['X-Test' => 'actual']))->toNotHaveHeader('X-Test'),
        fn () => expectResponse(new Response(200, ['Content-Type' => 'text/plain']))->toHaveContentType('application/json'),
        fn () => expectResponse(new Response(200))->toBeJsonResponse(),
        fn () => expectResponse(new Response(200, [], 'actual'))->toHaveBody('expected'),
        fn () => expectResponse(new Response(200, [], 'actual'))->toContainBody('missing'),
        fn () => expectResponse(new Response(200, [], 'actual'))->toHaveEmptyBody(),
        fn () => expectResponse(new Response(200, [], 'not json'))->toHaveValidJson(),
        fn () => expectResponse(new Response(200, [], '{"id":1}'))->toHaveJson(['id' => 2]),
        fn () => expectResponse(new Response(200, [], '{"id":1}'))->toHaveExactJson(['id' => 2]),
        fn () => expectResponse(new Response(200, [], '{"id":1}'))->toHaveJsonPath('id', 2),
        fn () => expectResponse(new Response(200, [], '{"id":1}'))->toHaveJsonPath('id', 1, 2),
        fn () => expectResponse(new Response(200, [], '{"name":"Felipe"}'))->toHaveJsonPathContaining('name', 'missing'),
        fn () => expectResponse(new Response(200, [], '{"id":1}'))->toHaveJsonPathContaining('id', '1'),
        fn () => expectResponse(new Response(200, [], '{}'))->toHaveJsonKeys('missing'),
        fn () => expectResponse(new Response(200, [], '{"id":1}'))->toHaveJsonCount(1, 'id'),
        fn () => expectResponse(new Response(200, [], '{"items":[]}'))->toHaveJsonCount(1, 'items'),
        fn () => expectResponse(new Response(200, [], '{"errors":"invalid"}'))->toHaveValidationErrors(),
        fn () => expectResponse(new Response(200, [], '{"errors":[]}'))->toHaveValidationErrors(),
        fn () => expectResponse(new Response(200, [], '{"errors":{"name":"Required"}}'))->toHaveValidationErrors('email'),
        fn () => expectResponse(new Response(200, [], '{"message":"actual"}'))->toHaveErrorMessage('expected'),
        fn () => expectResponse(new Response(200, [], '{"message":"actual"}'))->toContainErrorMessage('missing'),
        fn () => expectResponse(new Response(200, ['Location' => '/new']))->toRedirectTo('/new'),
        fn () => expectResponse(new Response(301, ['Location' => '/old']))->toRedirectTo('/new'),
    ];

    foreach ($invalidCases as $invalidCase) {
        expect($invalidCase)->toThrow(AssertionFailedError::class);
    }
});

it('preserves the stream position through the typed API', function () {
    $response = new Response(200, [], 'abcdef');
    $response->getBody()->seek(3);

    expectResponse($response)->toHaveBody('abcdef')->toContainBody('cde');
    expect($response->getBody()->tell())->toBe(3);
});

it('rejects nonseekable streams through the typed API', function () {
    $response = new Response(200, [], new NoSeekStream(Utils::streamFor('abc')));

    expect(fn () => expectResponse($response)->toHaveBody('abc'))->toThrow(AssertionFailedError::class);
});
