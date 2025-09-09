export const API_BASE_URL = 'http://localhost:8000/api/v1';

export const AUTH_TOKEN_KEY = 'tiao_carreiro_token';

export const ROUTES = {
  HOME: '/',
  LOGIN: '/login',
  ADMIN: '/admin',
  ADMIN_SONGS: '/admin/songs',
  ADMIN_SUGGESTIONS: '/admin/suggestions',
} as const;

export const SONG_STATUS = {
  ACTIVE: 'active',
  INACTIVE: 'inactive',
} as const;

export const SUGGESTION_STATUS = {
  PENDING: 'pending',
  APPROVED: 'approved',
  REJECTED: 'rejected',
} as const;

export const SUGGESTION_STATUS_LABELS = {
  [SUGGESTION_STATUS.PENDING]: 'Pendente',
  [SUGGESTION_STATUS.APPROVED]: 'Aprovada',
  [SUGGESTION_STATUS.REJECTED]: 'Rejeitada',
} as const;

export const ITEMS_PER_PAGE = 10;

export const THEME_COLORS = {
  primary: {
    50: '#fefdf8',
    100: '#fefbf1',
    200: '#fcf4dd',
    300: '#f9e8b7',
    400: '#f3d485',
    500: '#ecb95d',
    600: '#e09d42',
    700: '#bc7f36',
    800: '#976534',
    900: '#7a532e',
    950: '#442b15',
  },
  accent: {
    50: '#f6f8f4',
    100: '#eaefe5',
    200: '#d6dfcc',
    300: '#b8c7a8',
    400: '#94a77f',
    500: '#748b5f',
    600: '#5a6f49',
    700: '#47583c',
    800: '#3b4833',
    900: '#323d2d',
    950: '#1a2017',
  },
} as const;

export const YOUTUBE_URL_REGEX = /^https?:\/\/(?:(?:www|m)\.)?(?:youtube\.com\/(?:watch\?.*v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})(?:\S+)?$/;

export const DEFAULT_SONG_THUMBNAIL = '/placeholder-song.jpg';

export const MESSAGES = {
  SONG_CREATED: 'Música criada com sucesso!',
  SONG_UPDATED: 'Música atualizada com sucesso!',
  SONG_DELETED: 'Música removida com sucesso!',
  SUGGESTION_CREATED: 'Sugestão enviada com sucesso!',
  SUGGESTION_UPDATED: 'Sugestão atualizada com sucesso!',
  LOGIN_SUCCESS: 'Login realizado com sucesso!',
  LOGIN_ERROR: 'Credenciais inválidas',
  LOGOUT_SUCCESS: 'Logout realizado com sucesso!',
  NETWORK_ERROR: 'Erro de conexão. Tente novamente.',
  UNAUTHORIZED: 'Acesso não autorizado',
  SERVER_ERROR: 'Erro interno do servidor',
  INVALID_YOUTUBE_URL: 'URL do YouTube inválida',
  REQUIRED_FIELD: 'Este campo é obrigatório',
} as const;