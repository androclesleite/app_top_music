# Instruções de Setup - Top 5 Tião Carreiro API

## 1. Instalação do SQLite

Para usar o banco SQLite, você precisa instalar o PHP SQLite extension:

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install php-sqlite3 sqlite3

# CentOS/RHEL
sudo yum install php-pdo php-sqlite3

# Alpine
sudo apk add php-sqlite3 php-pdo_sqlite
```

## 2. Criar o arquivo de banco

```bash
touch database/database.sqlite
```

## 3. Executar as migrações

```bash
php artisan migrate
```

## 4. Criar um usuário admin (opcional)

```bash
php artisan tinker
```

No tinker, execute:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = Hash::make('password123');
$user->save();
```

## 5. Testar a API

### Rotas Públicas

- `GET /api/v1/songs` - Listar músicas (outras, não top 5)
- `GET /api/v1/songs/top-five` - Listar top 5
- `GET /api/v1/songs/{id}` - Detalhes de uma música
- `POST /api/v1/songs/{id}/play` - Contabilizar reprodução
- `POST /api/v1/suggestions` - Criar sugestão

### Autenticação

- `POST /api/v1/auth/login` - Login (retorna token)
- `GET /api/v1/auth/me` - Dados do usuário logado
- `POST /api/v1/auth/logout` - Logout

### Rotas Protegidas (Admin)

Headers necessários:
```
Authorization: Bearer {token}
Accept: application/json
```

- `POST /api/v1/songs` - Criar música
- `PUT /api/v1/songs/{id}` - Atualizar música
- `DELETE /api/v1/songs/{id}` - Deletar música
- `PUT /api/v1/songs/positions` - Atualizar posições do top 5
- `GET /api/v1/suggestions` - Listar sugestões
- `GET /api/v1/suggestions/pending` - Sugestões pendentes
- `PUT /api/v1/suggestions/{id}` - Aprovar/rejeitar sugestão

## 6. Exemplo de Payloads

### Login
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

### Criar Música
```json
{
    "title": "Pagode em Brasília",
    "youtube_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "position": 1
}
```

### Criar Sugestão
```json
{
    "title": "Nova Música",
    "youtube_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "suggested_by": "João Silva"
}
```

### Aprovar/Rejeitar Sugestão
```json
{
    "action": "approve"
}
```

### Atualizar Posições Top 5
```json
{
    "positions": {
        "1": 1,
        "2": 2,
        "3": 3,
        "4": 4,
        "5": 5
    }
}
```

## 7. Configurações CORS

O CORS está configurado para aceitar todas as origens durante desenvolvimento. Para produção, configure adequadamente no arquivo `config/cors.php`.

## 8. Executar o servidor

```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000/api/v1/`