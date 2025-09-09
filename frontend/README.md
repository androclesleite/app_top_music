# Top 5 Tião Carreiro - Frontend

Frontend React para o projeto Top 5 Tião Carreiro, uma homenagem ao maior violeiro do Brasil e suas interpretações mais marcantes da música sertaneja raiz.

## 🎵 Sobre o Projeto

Este projeto é uma aplicação web completa que permite aos usuários:
- Visualizar o Top 5 das melhores músicas de Tião Carreiro
- Explorar outras interpretações marcantes (paginadas)
- Sugerir novas músicas para serem incluídas na lista
- [Admin] Gerenciar músicas e sugestões através de um painel administrativo

## 🚀 Tecnologias Utilizadas

### Core
- **React 18** - Biblioteca principal
- **TypeScript** - Tipagem estática
- **Vite** - Build tool e dev server
- **React Router DOM** - Roteamento

### UI/UX
- **Tailwind CSS** - Framework de CSS utilitário
- **shadcn/ui** - Componentes UI reutilizáveis
- **Lucide React** - Ícones
- **Tailwind CSS Animate** - Animações

### Forms & Validation
- **React Hook Form** - Gerenciamento de formulários
- **@hookform/resolvers** - Resolvers para validação
- **Zod** - Schema validation

### HTTP & State
- **Axios** - Cliente HTTP
- **Context API** - Gerenciamento de estado global
- **Custom Hooks** - Lógica reutilizável

### Testing
- **Vitest** - Framework de testes
- **@testing-library/react** - Testes de componentes
- **@testing-library/jest-dom** - Matchers personalizados
- **jsdom** - Ambiente DOM para testes

## 📁 Estrutura do Projeto

```
src/
├── components/          # Componentes reutilizáveis
│   ├── ui/             # Componentes base do shadcn/ui
│   ├── layout/         # Componentes de layout (Header, Footer, Sidebar)
│   ├── SongCard.tsx    # Card de música com thumbnail
│   ├── TopFiveSongs.tsx # Seção do Top 5
│   ├── OtherSongs.tsx  # Lista paginada das outras músicas
│   ├── SuggestionForm.tsx # Formulário de sugestão
│   └── ...
├── context/            # Contexts do React
│   └── AuthContext.tsx # Context de autenticação
├── hooks/              # Custom hooks
│   ├── useAuth.ts      # Hook de autenticação
│   ├── useSongs.ts     # Hook para gerenciar músicas
│   └── useSuggestions.ts # Hook para sugestões
├── lib/                # Utilitários
│   └── utils.ts        # Funções auxiliares
├── pages/              # Páginas da aplicação
│   ├── Home.tsx        # Página inicial
│   ├── Login.tsx       # Página de login
│   ├── Admin.tsx       # Dashboard administrativo
│   └── NotFound.tsx    # Página 404
├── services/           # Serviços
│   └── api.ts          # Configuração do Axios
├── types/              # Definições de tipos TypeScript
│   └── index.ts        # Tipos principais
├── constants/          # Constantes da aplicação
│   └── index.ts        # URLs, mensagens, etc.
└── main.tsx           # Ponto de entrada da aplicação
```

## 🎨 Design System

### Tema Sertanejo
O projeto utiliza uma paleta de cores inspirada na música sertaneja:
- **Primary**: Tons de âmbar/laranja (#F59E0B)
- **Secondary**: Dourado claro
- **Accent**: Verde oliva
- **Background**: Gradientes suaves

### Componentes
- Cards de música com thumbnails do YouTube
- Badges para status e posições
- Formulários validados
- Tabelas responsivas com paginação
- Loading states e skeletons
- Toasts para feedback
- Modais para ações administrativas

## 🔧 Configuração e Desenvolvimento

### Pré-requisitos
- Node.js 18+ 
- npm ou yarn

### Instalação
```bash
# Clone o repositório
git clone <url-do-repositorio>

# Entre na pasta do frontend
cd frontend

# Instale as dependências
npm install
```

### Scripts Disponíveis

```bash
# Desenvolvimento (http://localhost:3000)
npm run dev

# Build para produção
npm run build

# Preview do build
npm run preview

# Testes
npm run test

# Testes com interface
npm run test:ui

# Testes em modo watch
npm run test:watch

# Linting
npm run lint
```

### Variáveis de Ambiente

Crie um arquivo `.env` na raiz do projeto:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

## 🔐 Autenticação

O sistema utiliza JWT tokens para autenticação:
- Tokens são armazenados no localStorage
- Interceptors do Axios adicionam automaticamente o token
- Redirect automático para login em caso de 401
- Context API gerencia o estado de autenticação

## 📱 Funcionalidades

### Área Pública
- **Home**: Top 5 + outras músicas + formulário de sugestão
- **Top 5**: Cards destacados com posições e badges especiais
- **Outras Músicas**: Lista paginada com busca
- **Sugestão**: Formulário completo com validação de URLs do YouTube

### Área Administrativa (Protegida)
- **Dashboard**: Overview com estatísticas e ações rápidas
- **Gerenciar Músicas**: CRUD completo com upload de thumbnails
- **Sugestões**: Aprovação/rejeição de sugestões dos usuários
- **Reordenação**: Drag & drop para reorganizar o Top 5

### Recursos Especiais
- **Play Count**: Tracking de reproduções
- **YouTube Integration**: Thumbnails e links automáticos
- **Responsive Design**: Funciona em todos os dispositivos
- **Loading States**: UX otimizada com skeletons
- **Error Handling**: Tratamento robusto de erros
- **Accessibility**: ARIA labels e navegação por teclado

## 🎯 APIs Consumidas

### Endpoints Principais
- `GET /api/v1/songs/top-five` - Top 5 músicas
- `GET /api/v1/songs?page=1` - Outras músicas (paginado)
- `POST /api/v1/suggestions` - Criar sugestão
- `POST /api/v1/auth/login` - Login
- `GET /api/v1/auth/me` - Dados do usuário

### Padrões
- Todas as requisições incluem loading states
- Tratamento de erro padronizado
- Paginação consistente
- Filtros e busca em tempo real

## 🧪 Testes

```bash
# Executar todos os testes
npm run test

# Testes em modo watch
npm run test:watch

# Testes com cobertura
npm run test:coverage

# Testes com UI
npm run test:ui
```

### Tipos de Teste
- **Unit Tests**: Componentes individuais
- **Integration Tests**: Fluxos completos
- **E2E Tests**: (planejado para o futuro)

## 📦 Build e Deploy

### Build de Produção
```bash
npm run build
```

### Docker
```dockerfile
# Dockerfile já configurado para produção
# Nginx + build otimizado
```

### Deploy
O projeto está configurado para deploy em:
- **Vercel** (recomendado)
- **Netlify**
- **Docker containers**

## 🔄 Roadmap

### Funcionalidades Futuras
- [ ] Drag & drop para reordenar Top 5
- [ ] Upload de thumbnails customizadas
- [ ] Sistema de comentários
- [ ] Integração com Spotify/Apple Music
- [ ] PWA (Progressive Web App)
- [ ] Dark/Light mode toggle
- [ ] Compartilhamento social
- [ ] Estatísticas avançadas

### Melhorias Técnicas
- [ ] React Query para cache
- [ ] Virtual scrolling para listas grandes
- [ ] Bundle splitting otimizado
- [ ] Service Workers
- [ ] Performance monitoring

## 👥 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Add: MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto é uma homenagem educativa ao legado de Tião Carreiro e está disponível sob licença MIT.

---

**"A música sertaneja é a alma do Brasil, e Tião Carreiro foi o maior intérprete dessa alma."**