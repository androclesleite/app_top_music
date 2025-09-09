# 🧪 Guia de Testes - Top 5 Tião Carreiro

## 📋 Visão Geral

Este projeto implementa uma suíte completa de testes automatizados para o backend Laravel, cobrindo todos os aspectos da aplicação com mais de **150 testes** organizados em Feature Tests, Unit Tests e Model Tests.

## 🏗️ Estrutura de Testes

### 📁 Diretórios

```
tests/
├── Feature/                     # Testes de integração (API endpoints)
│   ├── AuthTest.php            # Autenticação (login, logout, me)
│   ├── SongTest.php            # CRUD de músicas e top 5
│   └── SuggestionTest.php      # Workflow de sugestões
├── Unit/                       # Testes unitários de lógica
│   ├── AuthServiceTest.php     # Lógica de autenticação
│   ├── SongServiceTest.php     # Lógica de negócio músicas
│   ├── SuggestionServiceTest.php # Lógica de sugestões
│   ├── SongRepositoryTest.php  # Queries e data access
│   ├── SuggestionRepositoryTest.php # Data access sugestões
│   └── Models/                 # Testes de models
│       ├── SongTest.php        # Model Song
│       ├── SongSuggestionTest.php # Model SongSuggestion
│       └── UserTest.php        # Model User
├── TestCase.php                # Base class com helpers
└── database/factories/         # Factories para testes
    ├── SongFactory.php
    └── SongSuggestionFactory.php
```

## 🎯 Cobertura de Testes

### **Feature Tests** (49 testes)
- ✅ **AuthTest** - 15 testes
  - Login com credenciais válidas/inválidas
  - Logout e invalidação de tokens
  - Acesso a rotas protegidas
  - Profile de usuário autenticado

- ✅ **SongTest** - 24 testes
  - Listagem de top 5 e outras músicas
  - CRUD completo (admin only)
  - Reordenação do top 5
  - Incremento de plays
  - Validações e edge cases

- ✅ **SuggestionTest** - 22 testes
  - Criação de sugestões (público)
  - Gestão de sugestões (admin)
  - Aprovação/rejeição
  - Stats e filtros

### **Unit Tests** (82+ testes)
- ✅ **Services** - 35 testes
  - `AuthService`: Autenticação, logout, refresh token
  - `SongService`: Lógica do top 5, CRUD, validações
  - `SuggestionService`: Workflow approval, validações

- ✅ **Repositories** - 39 testes
  - `SongRepository`: Queries, scopes, paginação
  - `SuggestionRepository`: Filtros, ordenação, stats

### **Model Tests** (72+ testes)
- ✅ **Song Model** - 22 testes
  - Fillable, casts, relationships
  - Scopes (topFive, others)
  - YouTube URL extraction
  - Accessors (thumbnail, video_id)

- ✅ **SongSuggestion Model** - 25 testes
  - Status constants e scopes
  - Relationships com User
  - YouTube URL processing
  - Factory states

- ✅ **User Model** - 25 testes
  - Authenticatable features
  - Sanctum token management
  - Relationships
  - Data protection

## 🔧 Setup e Configuração

### **1. Dependências de Sistema**

```bash
# Para ambiente completo, instalar SQLite
sudo apt-get install php8.3-sqlite3 sqlite3

# Verificar extensões disponíveis
php -m | grep -E "(sqlite|pdo)"
```

### **2. Configuração de Banco de Dados de Teste**

O arquivo `phpunit.xml` está configurado para usar SQLite in-memory:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
</php>
```

### **3. Factories de Dados**

#### SongFactory
```php
Song::factory()->create();                    // Song básica
Song::factory()->topFive()->create();         // Posição 1-5
Song::factory()->firstPlace()->create();      // Posição 1
Song::factory()->popular()->create();         // Alta contagem plays
```

#### SongSuggestionFactory
```php
SongSuggestion::factory()->create();          // Pending
SongSuggestion::factory()->approved()->create(); // Aprovada
SongSuggestion::factory()->rejected()->create(); // Rejeitada
SongSuggestion::factory()->reviewedBy($user)->create(); // Revisor específico
```

## 🚀 Executando os Testes

### **Comando Principal**
```bash
php artisan test
```

### **Por Tipo de Teste**
```bash
# Feature Tests
php artisan test --testsuite=Feature

# Unit Tests
php artisan test --testsuite=Unit

# Testes específicos
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Unit/SongServiceTest.php
```

### **Com Coverage (se xdebug disponível)**
```bash
php artisan test --coverage
```

### **Opções Úteis**
```bash
# Parar no primeiro erro
php artisan test --stop-on-failure

# Modo verboso
php artisan test --verbose

# Filtrar por nome
php artisan test --filter="test_user_can_login"
```

## 🎨 Helpers de Teste (TestCase)

### **Autenticação**
```php
$user = $this->authenticateUser();          // User normal
$admin = $this->authenticateAdmin();        // Admin user
```

### **API Requests**
```php
$response = $this->getApi('/api/v1/songs');
$response = $this->postApi('/api/v1/auth/login', $data);
$response = $this->putApi('/api/v1/songs/1', $data);
$response = $this->deleteApi('/api/v1/songs/1');
```

### **Assertions Customizadas**
```php
$this->assertApiResponse($response, 200);   // Success response
$this->assertApiError($response, 400);     // Error response
$this->assertValidationError($response, ['field']); // Validation
$this->assertUnauthorized($response);      // 401
```

### **Utilities**
```php
$url = $this->generateYouTubeUrl();        // URL válida
$url = $this->generateInvalidUrl();       // URL inválida
```

## 📊 Cenários de Teste Específicos

### **Top 5 Management**
- Reordenação automática ao inserir em posição ocupada
- Reorganização ao deletar música do top 5
- Validação de posições únicas (1-5)

### **YouTube Integration**
- Extração de video ID de diferentes formatos de URL
- Geração automática de thumbnails
- Validação de URLs válidas/inválidas

### **Suggestion Workflow**
- Prevenção de URLs duplicadas (songs + suggestions)
- Mudança de status com timestamp
- Criação automática de música ao aprovar

### **Authentication & Authorization**
- Token invalidation no logout
- Múltiplos tokens por usuário
- Refresh token mechanism

## 🔍 Debugging e Troubleshooting

### **Problemas Comuns**

1. **SQLite não encontrado**
   ```bash
   # Instalar extensão
   sudo apt-get install php8.3-sqlite3
   ```

2. **Timeout em testes**
   ```bash
   # Aumentar timeout no phpunit.xml
   <phpunit processTimeout="300">
   ```

3. **Memory issues**
   ```bash
   # Aumentar memory limit
   php -d memory_limit=512M artisan test
   ```

### **Logs de Debug**
```php
// Em testes, usar
dump($response->json());
dd($model->toArray());
\Log::info('Debug info', $data);
```

## 🏆 Métricas de Qualidade

### **Coverage Esperado**
- **Models**: 95%+ (relationships, scopes, accessors)
- **Services**: 90%+ (business logic, validations)
- **Repositories**: 85%+ (queries, filters)
- **Controllers**: 80%+ (via Feature tests)

### **Assertions por Teste**
- **Feature**: 3-8 assertions (flow completo)
- **Unit**: 1-3 assertions (comportamento específico)
- **Model**: 1-2 assertions (atributo/método específico)

## 📝 Convenções

### **Nomenclatura**
```php
public function test_descriptive_name_with_underscores(): void
public function test_service_method_with_valid_input(): void
public function test_model_relationship_returns_correct_data(): void
```

### **Estrutura AAA**
```php
public function test_example(): void
{
    // Arrange - Setup data
    $user = User::factory()->create();
    
    // Act - Execute action
    $result = $service->doSomething($user);
    
    // Assert - Verify results
    $this->assertTrue($result);
}
```

### **Data Providers**
```php
/** @dataProvider validInputProvider */
public function test_with_multiple_inputs($input, $expected): void
{
    $this->assertEquals($expected, $service->process($input));
}
```

## 🎵 Testes Específicos do Domínio

### **Música e Top 5**
- Posicionamento único no top 5
- Incremento thread-safe de plays
- Ordenação por popularidade fora do top 5

### **Sugestões**
- Duplicação de URLs (cross-table validation)
- Status workflow (pending → approved/rejected)
- Auto-criação de música aprovada

### **Autenticação**
- Hash de senha automático
- Token Sanctum management
- Role-based access (futura implementação)

---

## 📚 Recursos Adicionais

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Mockery Documentation](http://docs.mockery.io/)

**Implementado por:** Claude Code Assistant
**Data:** Setembro 2024
**Versão:** 1.0