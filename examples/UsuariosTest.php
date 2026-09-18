<?php
// Copy into your application's tests/Api/ directory.
// Register Psr7Expectations in tests/Pest.php first.
use GuzzleHttp\Client;

it('fetches a user over real HTTP', function () {
    $client = new Client([
        'base_uri' => getenv('TEST_API_URL') ?: 'http://localhost:8080',
        'http_errors' => false,
        'allow_redirects' => false,
        'timeout' => 10,
        'headers' => ['Accept' => 'application/json'],
    ]);
    $response = $client->request('GET', '/api/usuarios/1');
    expect($response)->toHaveStatus(200)->toBeJsonResponse()->toHaveJsonPath('data.id', 1);
});
