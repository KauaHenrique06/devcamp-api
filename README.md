# Laravel Back-end Starter Pack

API base em Laravel 13 com autenticação JWT, controle de acesso por roles/permissões e login social (Google e Microsoft).

**O que já vem pronto:**

- Login com e-mail e senha, emitindo token JWT (`tymon/jwt-auth`)
- Refresh de token e rota `/me`
- Roles e permissões com `spatie/laravel-permission`, sincronizadas pelo comando `permission:sync`
- CRUD de usuários e roles, listagem de permissões
- Login social com Google e Microsoft via `laravel/socialite`, ligado por feature flag
- Senha mestra opcional para logar como qualquer usuário (útil em desenvolvimento)
- Documentação OpenAPI gerada automaticamente com `dedoc/scramble`
- Mensagens de validação em pt-BR

---

## Sumário

- [Requisitos](#requisitos)
- [Rodando o projeto](#rodando-o-projeto)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [Rotas principais](#rotas-principais)
- [Permissões](#permissões)
- [Testes e qualidade de código](#testes-e-qualidade-de-código)
- [Login social: como obter as chaves](#login-social-como-obter-as-chaves)

---

## Requisitos

- PHP **8.3** ou superior, com as extensões `pdo_mysql` (ou `pdo_sqlite`), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json` e `bcmath`
- [Composer](https://getcomposer.org/) 2
- Um banco de dados: MySQL/MariaDB (o padrão do `.env.example`) ou SQLite
- Node.js e npm (só se for usar o `composer dev`, que sobe o Vite junto)

---

## Rodando o projeto

### 1. Clonar e instalar as dependências

```bash
git clone git@github.com:lucasAMBR/laravel-new-starter-pack.git
cd laravel-new-starter-pack
composer install
```

### 2. Criar o `.env`

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

- `key:generate` preenche o `APP_KEY`.
- `jwt:secret` adiciona o `JWT_SECRET` ao `.env`, que é usado para assinar os tokens. Sem ele, o login não funciona.

### 3. Configurar o banco de dados

**MySQL/MariaDB.** Crie o banco e ajuste o `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=starter_pack
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

> No `.env.example` o `DB_CONNECTION` vem vazio. Se ficar assim, o Laravel cai no padrão `sqlite`, então preencha com `mysql` se for esse o caso.

**SQLite.** Para algo rápido e local:

```bash
touch database/database.sqlite
```

```env
DB_CONNECTION=sqlite
```

Depois apague ou comente as outras linhas `DB_*`.

### 4. Rodar as migrations e os seeders

```bash
php artisan migrate --seed
php artisan permission:sync
```

O seeder cria duas roles (`admin` e `user`, no guard `api`) e dois usuários:

| E-mail              | Senha      | Role    |
| ------------------- | ---------- | ------- |
| `admin@example.com` | `password` | `admin` |
| `user@example.com`  | `password` | `user`  |

O `permission:sync` cria as permissões definidas em [config/permissions_sync.php](config/permissions_sync.php) e atribui todas à role `admin`. **Ele precisa rodar depois do seeder**, porque depende da role `admin` já existir.

### 5. Subir o servidor

```bash
php artisan serve
```

A API fica em `http://localhost:8000/api`. Para testar:

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "admin@example.com", "password": "password"}'
```

A resposta traz `data.token`. Use esse valor nas rotas protegidas com o header `Authorization: Bearer <token>`.

Se quiser subir o servidor, a fila e o Vite de uma vez, rode `npm install` uma vez e depois:

```bash
composer dev
```

### 6. Documentação da API

Com o servidor rodando em ambiente `local`, acesse **http://localhost:8000/docs/api**. A documentação é gerada pelo Scramble direto do código (controllers, form requests e resources), então está sempre atualizada.

Para exportar a especificação OpenAPI em `api.json`:

```bash
php artisan scramble:export
```

---

## Variáveis de ambiente

As variáveis padrão do Laravel estão comentadas na [documentação oficial](https://laravel.com/docs/configuration). Estas são as específicas do starter pack:

| Variável              | Para que serve                                                                                                                            |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `JWT_SECRET`          | Chave que assina os tokens JWT. Gere com `php artisan jwt:secret`.                                                                        |
| `JWT_TTL`             | Validade do token em minutos. Padrão: `60`.                                                                                               |
| `JWT_REFRESH_TTL`     | Janela em minutos em que o token ainda pode ser renovado. Padrão: `20160` (14 dias).                                                      |
| `MASTER_PASSWORD`     | **Hash bcrypt** de uma senha mestra que permite logar como qualquer usuário. Deixe vazio para desativar. Veja abaixo como gerar.          |
| `SOCIAL_LOGIN_ENABLE` | Liga (`true`) ou desliga (`false`) as rotas de login social. Desligado, elas respondem 404.                                               |
| `GOOGLE_*`            | Credenciais do login com Google. Veja [Login social](#login-social-como-obter-as-chaves).                                                 |
| `MICROSOFT_*`         | Credenciais do login com Microsoft. Veja [Login social](#login-social-como-obter-as-chaves).                                              |

### Gerando a senha mestra

O `MASTER_PASSWORD` guarda o **hash**, não a senha em texto. Para gerar:

```bash
php artisan tinker --execute="echo Hash::make('minha-senha-mestra');"
```

Cole o resultado entre aspas simples no `.env` (o hash tem `$`, que o `.env` tentaria interpretar se estivesse em aspas duplas):

```env
MASTER_PASSWORD='$2y$12$....'
```

> Não use a senha mestra em produção. Qualquer pessoa que a conheça entra como qualquer usuário.

---

## Rotas principais

Todas têm o prefixo `/api`. A lista completa, com payloads e respostas, está em `/docs/api`.

| Método | Rota                                   | Autenticação | Permissão          |
| ------ | -------------------------------------- | ------------ | ------------------ |
| POST   | `/auth/login`                          | —            | —                  |
| POST   | `/auth/refresh-token`                  | —            | —                  |
| GET    | `/auth/me`                             | JWT          | —                  |
| GET    | `/social-auth/{provider}/redirect`     | —            | —                  |
| POST   | `/social-auth/{provider}/callback`     | —            | —                  |
| GET    | `/permissions`                         | JWT          | `permissions.view` |
| GET    | `/roles`, `/roles/{id}`                | JWT          | `roles.view`       |
| POST   | `/roles`                               | JWT          | `roles.create`     |
| PUT    | `/roles/{id}`                          | JWT          | `roles.update`     |
| POST   | `/roles/{id}/permissions`              | JWT          | `roles.update`     |
| DELETE | `/roles/{id}`                          | JWT          | `roles.delete`     |
| GET    | `/users`, `/users/{id}`                | JWT          | `user.view`        |
| POST   | `/users`                               | JWT          | `user.create`      |
| PUT    | `/users/{id}`                          | JWT          | `user.update`      |
| DELETE | `/users/{id}`                          | JWT          | `user.delete`      |

---

## Permissões

Para adicionar permissões de um módulo novo:

1. Inclua as chaves em [config/permissions_sync.php](config/permissions_sync.php), no formato `recurso.acao => 'Label legível'`.
2. Rode `php artisan permission:sync`.
3. Proteja as rotas com o middleware `can:recurso.acao`, como nos arquivos em [routes/api/](routes/api/).

O comando é idempotente: pode rodar quantas vezes quiser, ele só cria o que ainda não existe e garante que a role `admin` tenha todas as permissões.

---

## Testes e qualidade de código

```bash
php artisan test       # roda a suíte (Pest)
./vendor/bin/pint      # formata o código no padrão Laravel
```

---

## Login social: como obter as chaves

### Como o fluxo funciona

O login social roda em modo *stateless*, pensado para um front-end separado (SPA ou app mobile). Quem recebe o retorno do provedor é o **front-end**, não a API:

1. O front chama `GET /api/social-auth/{provider}/redirect` (`provider` é `google` ou `microsoft`) e recebe em `data.url` a URL de login do provedor.
2. O front redireciona o usuário para essa URL.
3. Depois do login, o provedor manda o usuário de volta para a **redirect URI** configurada (por exemplo `http://localhost:3000/auth/google/callback`), com um `code` na query string.
4. O front pega esse `code` e envia para `POST /api/social-auth/{provider}/callback` com o corpo `{ "code": "..." }`.
5. A API troca o `code` pelos dados do usuário, cria ou vincula a conta e devolve um token JWT, no mesmo formato do login normal.

Na hora de vincular a conta, a API procura primeiro pelo `provider` + `provider_id`. Se não achar, procura pelo e-mail e associa o provedor a esse usuário. Se ainda não achar, cria um usuário novo com a role `user`.

**Redirect URI web e mobile.** Cada provedor aceita duas redirect URIs: a web (`*_REDIRECT_URI`) e a mobile (`*_MOBILE_REDIRECT_URI`, por exemplo `meuapp://auth/google/callback`). Por padrão a API usa a web. Para usar a mobile, envie `redirect_uri` como query string no `/redirect` e no corpo do `/callback`. A API só aceita os dois valores que estão no `.env` e recusa qualquer outro com 422. **A mesma URI precisa ser usada nas duas chamadas e estar cadastrada no provedor.**

Antes de tudo, deixe a feature ligada no `.env`:

```env
SOCIAL_LOGIN_ENABLE=true
```

### Google

Você vai precisar de uma conta Google e de acesso ao [Google Cloud Console](https://console.cloud.google.com/).

1. **Crie ou escolha um projeto.** No seletor de projetos no topo, clique em **Novo projeto**, dê um nome e crie.
2. **Configure a tela de consentimento OAuth.** Vá em **APIs e serviços → Tela de permissão OAuth** (em consoles mais novos o menu se chama **Google Auth Platform**).
   - Preencha o nome do app, o e-mail de suporte e o e-mail de contato do desenvolvedor.
   - Em **Público** (*Audience*), escolha **Externo** se qualquer conta Google puder logar, ou **Interno** se for só para contas do seu Google Workspace.
   - Os escopos necessários são os básicos: `openid`, `email` e `profile`. Eles já são liberados por padrão.
   - Enquanto o app estiver em modo **Teste**, só os e-mails cadastrados em **Usuários de teste** conseguem logar. Adicione o seu lá. Para liberar para todo mundo, publique o app.
3. **Crie as credenciais.** Vá em **APIs e serviços → Credenciais → Criar credenciais → ID do cliente OAuth**.
   - Tipo de aplicativo: **Aplicativo da Web**.
   - Em **Origens JavaScript autorizadas**, adicione a URL do front-end (ex.: `http://localhost:3000`).
   - Em **URIs de redirecionamento autorizados**, adicione exatamente a URL que você vai colocar em `GOOGLE_REDIRECT_URI` (ex.: `http://localhost:3000/auth/google/callback`).
4. **Copie os valores.** Ao salvar, o Google mostra o **ID do cliente** e a **Chave secreta do cliente**. Copie os dois. A chave secreta pode ser consultada depois na página da credencial.

Preencha o `.env`:

```env
GOOGLE_CLIENT_ID=xxxxxxxxxxxx-xxxxxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URI=http://localhost:3000/auth/google/callback
GOOGLE_MOBILE_REDIRECT_URI=meuapp://auth/google/callback
```

> **Sobre a URI mobile no Google:** clientes do tipo "Aplicativo da Web" não aceitam esquemas customizados como `meuapp://`. Para login direto no app, você precisa de um ID de cliente separado do tipo **Android** ou **iOS**, e o fluxo nativo usa PKCE em vez de chave secreta. Se o app mobile for abrir o login num navegador e voltar por uma URL `https://` (*deep link* / *universal link*), use essa URL `https://` no `GOOGLE_MOBILE_REDIRECT_URI` e cadastre-a no mesmo cliente web.

**Erros comuns:**

- `redirect_uri_mismatch`: a URI enviada não está idêntica à cadastrada. Confira `http` x `https`, porta e barra no final.
- `access_denied` / "app não verificado": o app está em modo Teste e o seu e-mail não está na lista de usuários de teste.

### Microsoft (Entra ID / Azure AD)

Você vai precisar de uma conta Microsoft com acesso ao [Microsoft Entra admin center](https://entra.microsoft.com/) ou ao [portal do Azure](https://portal.azure.com/). Uma conta pessoal (Outlook/Hotmail) também serve: ao acessar, um diretório padrão é criado para você.

1. **Registre o aplicativo.** Vá em **Identidade → Aplicativos → Registros de aplicativo → Novo registro** (no portal do Azure: **Microsoft Entra ID → Registros de aplicativo**).
   - **Nome:** o nome que vai aparecer para o usuário na tela de login.
   - **Tipos de conta com suporte:** escolha conforme quem vai logar. Essa escolha define o `MICROSOFT_TENANT_ID` (veja a tabela abaixo).
   - **URI de redirecionamento:** plataforma **Web**, com o valor de `MICROSOFT_REDIRECT_URI` (ex.: `http://localhost:3000/auth/microsoft/callback`).
2. **Copie os IDs.** Na tela **Visão geral** do app:
   - **ID do aplicativo (cliente)** → `MICROSOFT_CLIENT_ID`
   - **ID do diretório (locatário)** → `MICROSOFT_TENANT_ID`, só se o app for de um único locatário
3. **Crie a chave secreta.** Vá em **Certificados e segredos → Segredos do cliente → Novo segredo do cliente**, dê uma descrição e escolha a validade.
   - Copie o **Valor** na hora (não o "ID do segredo"). Ele só aparece uma vez. Se perder, crie outro.
   - Anote a data de expiração. Quando o segredo vencer, o login com Microsoft para de funcionar até você gerar um novo e atualizar o `.env`.
4. **Confira as permissões.** Em **Permissões de API**, o `User.Read` do Microsoft Graph já vem adicionado e é suficiente para ler nome, e-mail e foto.
5. **(Opcional) Redirect mobile.** Em **Autenticação → Adicionar uma plataforma**, escolha **Aplicativos móveis e da área de trabalho** e adicione a URI customizada (ex.: `meuapp://auth/microsoft/callback`).

**Qual `MICROSOFT_TENANT_ID` usar:**

| Tipo de conta escolhido no registro                              | `MICROSOFT_TENANT_ID`            |
| ---------------------------------------------------------------- | -------------------------------- |
| Contas de qualquer organização **e** contas pessoais Microsoft   | `common`                         |
| Contas de qualquer organização (só corporativas/escolares)       | `organizations`                  |
| Somente contas pessoais Microsoft                                | `consumers`                      |
| Somente contas deste diretório (um único locatário)              | o **ID do diretório (locatário)** |

Preencha o `.env`:

```env
MICROSOFT_CLIENT_ID=00000000-0000-0000-0000-000000000000
MICROSOFT_CLIENT_SECRET=xxxxx~xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MICROSOFT_TENANT_ID=common
MICROSOFT_REDIRECT_URI=http://localhost:3000/auth/microsoft/callback
MICROSOFT_MOBILE_REDIRECT_URI=meuapp://auth/microsoft/callback
```

**Erros comuns:**

- `AADSTS50011`: a redirect URI não bate com nenhuma cadastrada no app.
- `AADSTS7000215` (*invalid client secret*): você copiou o "ID do segredo" em vez do "Valor", ou o segredo expirou.
- `AADSTS50020` / `AADSTS50194`: o tipo de conta do registro não combina com o `MICROSOFT_TENANT_ID`. Por exemplo, o app é de um único locatário e o `.env` está com `common`.
- A API responde 422 "The provider doesn't return an e-mail!": a conta não tem e-mail associado. Acontece com algumas contas corporativas sem caixa de correio.

### Dicas gerais

- **Nunca commite chaves secretas.** O `.env` já está no `.gitignore`. No `.env.example`, deixe os valores `*_CLIENT_ID` e `*_CLIENT_SECRET` vazios.
- Depois de mexer no `.env`, rode `php artisan config:clear` se o cache de configuração estiver ativo.
- Em produção, cadastre nos provedores as URIs `https://` do domínio real, além das de localhost.
