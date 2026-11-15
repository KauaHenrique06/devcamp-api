# CLAUDE.md

API Laravel (JWT, Spatie Permission, Scramble, multi-tenancy por organização). Siga os padrões abaixo em todo recurso novo ou alterado. Use os módulos `Organization` e `Project` como referência.

## Estrutura de um recurso

Para um recurso `Foo`:

- `app/Http/Controllers/Foo/Foo.php`: controller com o mesmo nome do model (o model é importado como `use App\Models\Foo as FooModel;`)
- `app/Http/Requests/Foo/Foo{Index,Show,Store,Update,Delete,...}Request.php`: um FormRequest por ação
- `app/Services/Foo/FooService.php`: regra de negócio. Recebe `array $data` (o `validated()`) e devolve Resource/Collection
- `app/Http/Resources/Foo/FooResource.php` e `FooCollection.php`
- `app/Policies/FooPolicy.php`: autorização (permissão Spatie + papel na organização)
- `routes/api/foos.php`: registrado em `routes/api/index.php` com `Route::prefix('/foos')->group(base_path('routes/api/foos.php'));`

## Rotas

- **Não use route model binding.** Os parâmetros de rota são strings simples: `/{id}`, `/{id}/members/{userId}`.
- O FormRequest valida o parâmetro de rota, e não o controller.
- Rotas autenticadas usam `auth.api`. Recursos que pertencem a uma organização (models com `Tenantable` + `organization_id`) também usam `organization`, que lê o header `X-Organization-Id`:

```php
Route::middleware(['auth.api', 'organization'])->group(function () {
    Route::get('/', [Foo::class, 'index']);
    Route::post('/', [Foo::class, 'store']);
    Route::get('/{id}', [Foo::class, 'show']);
    Route::put('/{id}', [Foo::class, 'update']);
    Route::delete('/{id}', [Foo::class, 'delete']);
});
```

## Requests

- `authorize()` sempre retorna `true`. A autorização fica na Policy, chamada no controller.
- Todo parâmetro de rota é mesclado no request em `prepareForValidation()` e validado em `rules()`. **A chave no request tem o mesmo nome do parâmetro na rota** (`{id}` → `'id'`). Exceção histórica: `{userId}` → `'user_id'` nos membros da organização. Em código novo, prefira nomes iguais.
- `attributes()` traz os nomes dos campos em português.
- Listagens usam os helpers de `app/Helpers/Requests/Utils` (`PerPageRuleHelper`, `PageRuleHelper`, `SearchRuleHelper`) em `rules()`, `attributes()` e `prepareForValidation()`.

```php
class FooShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => 'ID',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}
```

## Controllers

- Injetam o service no construtor e não têm regra de negócio. O fluxo é: `authorize` → service → `ReturnApi`.
- Sempre usam `$request->validated()` (nunca `$request->all()`/`input()`).
- **A Policy é chamada no controller com `$this->authorize()`**, nunca no service nem no request:
  - sem model/id: `$this->authorize('viewAny', FooModel::class);`
  - quando a policy precisa do registro em questão, passe o id validado: `$this->authorize('update', [FooModel::class, $request->validated('id')]);`. Nesse caso, o método da policy recebe `string $id` (não o model) e faz o `findOrFail` se precisar.
  - Argumentos extras vão no mesmo array: `[FooModel::class, $request->validated('id'), $request->validated('user_id')]`.
- Respostas sempre por `App\Builder\ReturnApi::success($data, 'Mensagem.', $status)` / `ReturnApi::error(...)`. Use 201 no `store`; no `delete`, `data` é `null`.
- Cada método tem o atributo Scramble `#[Endpoint(operationId: ..., title: ..., description: ...)]`:
  - `operationId` em camelCase: `{recursoNoPlural}{Acao}` (ex.: `projectsIndex`, `organizationMembersStore`).
  - `description` começa com `**operationId:** \`...\` — ` e descreve: o que faz, parâmetros aceitos, que o `id` vem do path validado no Request (sem route model binding), o schema da resposta (com o FQCN do Resource escapado com `\\`), os status de erro e as permissões exigidas.
  - Cada `return` de erro tem o docblock `@status` / `@body` para o Scramble.

```php
#[Endpoint(operationId: 'foosUpdate', title: 'Atualizar foo', description: '**operationId:** `foosUpdate` — ...')]
public function update(FooUpdateRequest $request): JsonResponse
{
    try {
        $this->authorize('update', [FooModel::class, $request->validated('id')]);

        $data = $this->fooService->update($request->validated());

        return ReturnApi::success($data, 'Foo atualizado com sucesso.');
    } catch (\Exception $e) {
        /**
         * @status 500
         *
         * @body array{error: true, message: string, data: mixed}
         */
        return ReturnApi::error('Erro ao atualizar foo: '.$e->getMessage(), null, 500);
    }
}
```

## Services

- Recebem `array $data` vindo do `validated()` e buscam o registro com `findOrFail($data['id'])`.
- Escritas rodam dentro de `DB::transaction(...)`.
- Retornam `FooResource` / `FooCollection`, ou `void` em deletes.

## Tenancy

- Models com a trait `Tenantable` passam pelo `TenantScope`. Em `Organization`, o usuário vê apenas as organizações das quais é membro. Nos demais models, o filtro é por `organization_id` da organização atual (`CurrentOrganization`, definida pelo middleware `organization`).
- Use `CurrentOrganization` (injetado) para ler a organização atual em services e policies.
- Policies têm `before()` liberando a role Spatie `admin`.

## Idioma

As mensagens para o usuário, os `attributes()`, os títulos e as descrições do Scramble são escritos em **português**.
