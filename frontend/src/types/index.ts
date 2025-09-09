export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

export interface Song {
  id: number;
  title: string;
  youtube_url: string;
  youtube_video_id: string;
  youtube_thumbnail: string;
  position: number;
  plays_count: number;
  is_top_five: boolean;
  created_at: string;
  updated_at: string;
}

export interface SongSuggestion {
  id: string;
  title: string;
  artist: string;
  youtube_url: string;
  thumbnail_url?: string;
  status: 'pending' | 'approved' | 'rejected';
  suggested_by_name?: string;
  suggested_by_email?: string;
  admin_notes?: string;
  created_at: string;
  updated_at: string;
}

export interface PaginatedResponse<T> {
  items: T[];
  total: number;
  page: number;
  per_page: number;
  pages: number;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    token: string;
  };
}

export interface CreateSongRequest {
  title: string;
  artist: string;
  youtube_url: string;
  position?: number;
}

export interface UpdateSongRequest extends Partial<CreateSongRequest> {
  is_active?: boolean;
}

export interface CreateSuggestionRequest {
  title: string;
  artist: string;
  youtube_url: string;
  suggested_by_name?: string;
  suggested_by_email?: string;
}

export interface UpdateSuggestionRequest {
  status: 'approved' | 'rejected';
  admin_notes?: string;
}

export interface ApiError {
  detail: string;
}

export interface SongFilters {
  search?: string;
  is_active?: boolean;
}

export interface SuggestionFilters {
  status?: 'pending' | 'approved' | 'rejected';
  search?: string;
}

export type ThemeMode = 'light' | 'dark';

export interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (credentials: LoginRequest) => Promise<void>;
  logout: () => void;
  refreshUser: () => Promise<void>;
}