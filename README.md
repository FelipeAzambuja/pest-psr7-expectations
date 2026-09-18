# NEWBGP Pest PSR-7 Expectations

Expectations para respostas PSR-7, incluindo respostas Guzzle, em testes Pest.
O pacote oferece uma API tipada para autocomplete e uma API dinâmica compatível
com o uso tradicional de `expect()`.

## Compatibilidade

- PHP `^8.1`.
- Pest `^2.0`, `^3.0` ou `^4.0`.
- PSR-7 `^1.1` ou `^2.0` (`psr/http-message`).
- Guzzle PSR-7 é usado nos testes e é uma dependência de desenvolvimento.

As versões listadas são as restrições declaradas pelo pacote. Teste a versão
específica do Pest e a implementação PSR-7 usadas pela sua aplicação antes de
publicar em produção. Não há compatibilidade declarada com Pest 5.

Nesta alteração, a validação automatizada foi executada com PHP 8.5.10, Pest
4.7.8, PSR HTTP Message 2.0 e Guzzle PSR-7 2.13.1. As demais versões permitidas
pelas restrições acima não foram executadas nesta alteração.

## Instalação

```bash
composer require --dev newbgp/pest-psr7-expectations guzzlehttp/guzzle
```

Para usar uma cópia local durante o desenvolvimento, adicione um repositório
path ao `composer.json` da aplicação:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../pest-psr7-expectations",
      "options": {"symlink": true}
    }
  ]
}
```

```bash
composer require --dev newbgp/pest-psr7-expectations:@dev guzzlehttp/guzzle
```

## Configuração

A API tipada (`expectResponse()`) é carregada automaticamente pelo Composer.
Para preservar as expectations dinâmicas do Pest, registre-as uma vez no
`tests/Pest.php`:

```php
<?php

use NewBGP\PestPsr7\Psr7Expectations;

Psr7Expectations::register();
```

O registro não é executado por `autoload.files`. Não é necessário um plugin
específico de CodeIgniter ou Laravel.

## API tipada — recomendada para DEVSENSE PHP Tools

Prefira a função tipada no VS Code com a extensão PHP Tools da DEVSENSE. O
editor consegue navegar para a implementação real, sugerir os métodos e
conhecer os parâmetros e o retorno `Psr7Expectation` em todo o encadeamento:

```php
<?php

use GuzzleHttp\Client;
use function NewBGP\PestPsr7\expectResponse;

it('busca um usuário', function () {
    $client = new Client([
        'base_uri' => 'http://localhost:8080',
        'http_errors' => false,
        'allow_redirects' => false,
    ]);

    $response = $client->request('GET', '/api/usuarios/1');

    expectResponse($response)
        ->toHaveStatus(200)
        ->toBeJsonResponse()
        ->toHaveJsonPath('data.id', 1);
});
```

`expectResponse()` recebe `Psr\Http\Message\ResponseInterface` e retorna
`NewBGP\PestPsr7\Psr7Expectation`. Todos os métodos são métodos PHP reais,
tipados e fluentes.

## API dinâmica do Pest

Depois de `Psr7Expectations::register()`, a API original continua disponível:

```php
expect($response)
    ->toHaveStatus(200)
    ->toBeJsonResponse()
    ->toHaveJsonPath('data.id', 1);
```

As duas formas podem ser usadas simultaneamente no mesmo projeto. A API
dinâmica encaminha a execução para `Psr7Expectation`, evitando duas
implementações das regras.

## Expectations disponíveis

- `toHaveStatus(int $expected)`
- `toHaveStatusIn(array $statuses)`
- `toBeInformational()`
- `toBeSuccessful()`
- `toBeRedirect()`
- `toBeClientError()`
- `toBeServerError()`
- `toHaveHeader(string $name, ?string $containing = null)`
- `toHaveHeaderValue(string $name, string $expected)`
- `toNotHaveHeader(string $name)`
- `toHaveContentType(string $expected)`
- `toBeJsonResponse()`
- `toHaveBody(string $expected)`
- `toContainBody(string $expected)`
- `toHaveEmptyBody()`
- `toHaveValidJson()`
- `toHaveJson(array $expected)`
- `toHaveExactJson(mixed $expected)`
- `toHaveJsonPath(string $path, mixed ...$expected)`
- `toHaveJsonPathContaining(string $path, string $expected)`
- `toHaveJsonKeys(string ...$paths)`
- `toHaveJsonCount(int $expected, string $path = '$')`
- `toHaveValidationErrors(string ...$fields)`
- `toHaveErrorMessage(string $expected, string $path = '$.message')`
- `toContainErrorMessage(string $expected, string $path = '$.message')`
- `toRedirectTo(string $location, ?int $status = null)`

## Semântica e limitações conhecidas

- O corpo é lido completamente e a posição original de streams seekable é
  restaurada. Streams não seekable são rejeitados explicitamente; faça
  buffering antes de passá-los às assertions.
- `toHaveJson` verifica um subconjunto recursivo com comparações estritas;
  índices numéricos são preservados.
- `toHaveExactJson` compara estritamente o resultado de
  `json_decode(..., true)`. Objetos e arrays JSON vazios decodificam ambos para
  `[]`.
- Os caminhos aceitam `data.0.id` e `$.data[0].id`, mas não são JSONPath
  completo: não há curingas, filtros ou escape para chaves com pontos. `null`
  existente é diferente de caminho ausente.
- `toHaveContentType` compara o media type exato, ignorando parâmetros e
  diferenças de maiúsculas/minúsculas.
- `toBeJsonResponse` aceita `application/json` e `application/*+json` e também
  valida o corpo como JSON.
- `toHaveHeader` usa substring opcional; `toHaveHeaderValue` compara a linha
  completa do header.
- `toBeRedirect` verifica a classe 3xx. `toRedirectTo` aceita somente 301, 302,
  303, 307 e 308, e compara `Location` literalmente. Desative
  `allow_redirects` no Guzzle para validar a resposta de redirect.
- `toHaveValidationErrors` espera um objeto `errors` não vazio e não impõe
  status HTTP; use `toHaveStatus` separadamente.
- A API tipada não fornece a sintaxe de negação do Pest (`->not`). Para negação,
  use a API dinâmica registrada.

## Desenvolvimento

```bash
composer install
composer validate --strict
composer dump-autoload
composer test
```

O projeto não declara ferramentas adicionais de análise ou formatação no
`composer.json`.

Use API e banco de teste separados. HTTP real não herda transações, mocks ou
configuração de ambiente do processo Pest. Configure também o servidor de
teste. Guzzle não aplica as regras CORS do navegador.

## Publicação

1. Revise nome, licença, README e execute testes nas versões que pretende
   suportar.
2. Envie os arquivos para um repositório Git, sem `vendor/`.
3. Crie a tag de versão e envie a tag.
4. Cadastre a URL do repositório em <https://packagist.org/packages/submit>.
