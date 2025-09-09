# 🎵 API Top 5 Tião Carreiro - Testes Postman

## 🚀 Setup Inicial

1. **Subir MySQL** e **Rodar migrations**:
```bash
# 1. Subir MySQL (via Docker ou local)
docker-compose up -d mysql

# 2. Rodar migrations e seeders
php artisan migrate
php artisan db:seed

# 3. Subir o servidor Laravel
php artisan serve
```

## 🔗 Base URL
```
http://localhost:8000/api/v1
```

---

## 📋 ROTAS PÚBLICAS (Sem autenticação)

### 1. **GET** Top 5 Músicas
```
GET /songs/top-five
```

### 2. **GET** Outras Músicas (paginado)
```
GET /songs?page=1&per_page=10
```

### 3. **GET** Detalhes de uma Música
```
GET /songs/{id}
```

### 4. **POST** Incrementar Play Count
```
POST /songs/{id}/play
```

### 5. **POST** Criar Sugestão
```
POST /suggestions
Content-Type: application/json

{
    "title": "Nova Música Sertaneja",
    "youtube_url": "https://www.youtube.com/watch?v=ABC123",
    "suggested_by": "Seu Nome"
}
```

---

## 🔐 AUTENTICAÇÃO

### 6. **POST** Login
```
POST /auth/login
Content-Type: application/json

{
    "email": "admin@techpines.com.br",
    "password": "123456"
}
```

**Resposta esperada:**
```json
{
    "user": {...},
    "token": "1|xxxxxxxxxxxxxxx",
    "token_type": "Bearer"
}
```

⚠️ **IMPORTANTE**: Copie o `token` da resposta para usar nas rotas protegidas!

---

## 🛡️ ROTAS PROTEGIDAS (Precisa de token)

**Header obrigatório em todas:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxx
```

### 7. **GET** Meus Dados
```
GET /auth/me
Authorization: Bearer {seu_token}
```

### 8. **POST** Logout
```
POST /auth/logout
Authorization: Bearer {seu_token}
```

### 9. **POST** Criar Música (Admin)
```
POST /songs
Authorization: Bearer {seu_token}
Content-Type: application/json

{
    "title": "Nova Música Top 5",
    "youtube_url": "https://www.youtube.com/watch?v=XYZ789",
    "position": 3,
    "plays_count": 1000000
}
```

### 10. **PUT** Atualizar Música (Admin)
```
PUT /songs/{id}
Authorization: Bearer {seu_token}
Content-Type: application/json

{
    "title": "Título Atualizado",
    "youtube_url": "https://www.youtube.com/watch?v=NEW123",
    "position": 2,
    "plays_count": 1500000
}
```

### 11. **DELETE** Remover Música (Admin)
```
DELETE /songs/{id}
Authorization: Bearer {seu_token}
```

### 12. **PUT** Reordenar Top 5 (Admin)
```
PUT /songs/positions
Authorization: Bearer {seu_token}
Content-Type: application/json

{
    "positions": [
        {"id": 1, "position": 1},
        {"id": 2, "position": 2},
        {"id": 3, "position": 3},
        {"id": 4, "position": 4},
        {"id": 5, "position": 5}
    ]
}
```

### 13. **GET** Listar Sugestões (Admin)
```
GET /suggestions
Authorization: Bearer {seu_token}
```

### 14. **GET** Sugestões Pendentes (Admin)
```
GET /suggestions/pending
Authorization: Bearer {seu_token}
```

### 15. **GET** Estatísticas Sugestões (Admin)
```
GET /suggestions/stats
Authorization: Bearer {seu_token}
```

### 16. **PUT** Aprovar/Rejeitar Sugestão (Admin)
```
PUT /suggestions/{id}
Authorization: Bearer {seu_token}
Content-Type: application/json

{
    "status": "approved"
}
# ou
{
    "status": "rejected"
}
```

---

## 👤 CREDENCIAIS PARA TESTE

```
Email: admin@techpines.com.br
Senha: 123456

Email: jansen@techpines.com.br  
Senha: password123
```

---

## ✅ CHECKLIST DE TESTES

### Rotas Públicas:
- [ ] Listar top 5 músicas
- [ ] Listar outras músicas
- [ ] Ver detalhes de música
- [ ] Incrementar play count
- [ ] Criar sugestão

### Autenticação:
- [ ] Login com credenciais corretas
- [ ] Login com credenciais erradas
- [ ] Acessar rota protegida sem token
- [ ] Acessar dados do usuário logado
- [ ] Logout

### Admin (Músicas):
- [ ] Criar nova música
- [ ] Atualizar música existente
- [ ] Deletar música
- [ ] Reordenar top 5

### Admin (Sugestões):
- [ ] Listar todas sugestões
- [ ] Ver sugestões pendentes
- [ ] Ver estatísticas
- [ ] Aprovar sugestão
- [ ] Rejeitar sugestão

---

## 🔧 Comandos Úteis

```bash
# Ver rotas da API
php artisan route:list --path=api

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Re-criar banco
php artisan migrate:fresh --seed
```