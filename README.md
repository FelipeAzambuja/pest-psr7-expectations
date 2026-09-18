# NEWBGP Pest PSR-7 Expectations

Expectations para respostas PSR-7, incluindo respostas Guzzle, em testes Pest.
Pacote-fonte preparado para Composer; **não publicado no Packagist**.

## Desenvolvimento

```bash
composer install
composer validate --strict
composer test
```

PHP >=8.1. Restrições declaradas para Pest 2/3/4; cada versão do Pest exige
sua própria versão mínima do PHP. Não foi adicionada compatibilidade com Pest 5
sem validação. A suíte foi incluída, mas não executada durante a criação do ZIP
porque o ambiente não tinha PHP/Composer. Valide na sua versão antes de publicar.

## Instalação local no projeto consumidor

Extraia este projeto ao lado da aplicação e acrescente ao composer.json dela:

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

Em tests/Pest.php:

```php
use NewBGP\PestPsr7\Psr7Expectations;

Psr7Expectations::register();
```

Não é necessário instalar um plugin específico de CodeIgniter/Laravel.
Não se usa autoload.files para executar registro implicitamente.

## Uso

```php
use GuzzleHttp\Client;

it('busca um usuário', function () {
    $client = new Client([
        'base_uri' => 'http://localhost:8080',
        'http_errors' => false,
        'allow_redirects' => false,
        'timeout' => 10,
    ]);

    $response = $client->request('GET', '/api/usuarios/1');

    expect($response)
        ->toHaveStatus(200)
        ->toBeJsonResponse()
        ->toHaveJsonPath('$.data.id', 1)
        ->toHaveJson(['data' => ['id' => 1]]);
});
```

Use API e banco de teste separados. HTTP real não herda transações, mocks ou
configuração de ambiente do processo Pest. Configure também o servidor de teste.
Guzzle não aplica as regras CORS do navegador.

## Expectations

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

## Semântica e limitações

- Corpo: leitura completa preserva posição do stream. Streams não seekable são
  rejeitados explicitamente; faça buffering antes de passá-los às assertions.
- toHaveJson: subconjunto recursivo estrito; arrays numéricos são comparados por índice.
- toHaveExactJson: comparação estrita do resultado de json_decode(..., true);
  a ordem das chaves importa; objetos e arrays vazios decodificam ambos para [].
- Caminhos: data.0.id ou $.data[0].id; não é JSONPath completo. Sem curingas,
  filtros ou escape para chaves com pontos. Null existente difere de caminho ausente.
- toHaveContentType compara o media type exato, ignorando parâmetros/maiúsculas.
- toBeJsonResponse aceita application/json e application/*+json.
- toHaveHeader usa substring opcional; toHaveHeaderValue compara a linha completa.
- toBeRedirect indica classe 3xx; toRedirectTo aceita somente 301/302/303/307/308
  e compara Location literalmente. Desative allow_redirects no Guzzle.
- toHaveValidationErrors espera um objeto errors não vazio e verifica chaves
  literais; não impõe status HTTP. Use toHaveStatus separadamente.
- Negação de expectations compostas pode passar quando apenas uma condição
  falha. Prefira assertions positivas explícitas para formato/header/body.

## Autocomplete

As extensões são dinâmicas: remover $this dos testes **não garante autocomplete**
para toHaveStatus e demais métodos em todo IDE. O suporte depende da integração
do Pest no editor ou de stubs específicos. As classes auxiliares são tipadas.
Use ClientInterface::request(), método real da interface, em vez de depender de
get()/post() mágicos para autocomplete.

## Publicar

1. Revise nome, licença, README e execute testes nas versões que pretende suportar.
2. Crie um repositório Git e envie os arquivos (sem vendor/).
3. Crie a tag v0.1.0 e envie a tag.
4. Cadastre a URL do repositório em https://packagist.org/packages/submit.

Após publicação, o consumidor poderá instalar sem o repositório path:

```bash
composer require --dev newbgp/pest-psr7-expectations
```
