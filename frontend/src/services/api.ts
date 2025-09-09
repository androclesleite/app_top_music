import axios, { AxiosInstance, AxiosResponse, AxiosError } from 'axios';
import { 
  API_BASE_URL, 
  AUTH_TOKEN_KEY, 
  MESSAGES,
  ROUTES 
} from '@/constants';
import {
  User,
  Song,
  SongSuggestion,
  PaginatedResponse,
  LoginRequest,
  LoginResponse,
  CreateSongRequest,
  UpdateSongRequest,
  CreateSuggestionRequest,
  UpdateSuggestionRequest,
  SongFilters,
  SuggestionFilters,
} from '@/types';

class ApiService {
  private api: AxiosInstance;

  constructor() {
    this.api = axios.create({
      baseURL: API_BASE_URL,
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    this.setupInterceptors();
  }

  private setupInterceptors(): void {
    // Request interceptor to add auth token
    this.api.interceptors.request.use(
      (config) => {
        const token = localStorage.getItem(AUTH_TOKEN_KEY);
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Response interceptor for error handling
    this.api.interceptors.response.use(
      (response) => response,
      (error: AxiosError) => {
        if (error.response?.status === 401) {
          localStorage.removeItem(AUTH_TOKEN_KEY);
          if (window.location.pathname !== ROUTES.LOGIN) {
            window.location.href = ROUTES.LOGIN;
          }
        }
        return Promise.reject(this.handleError(error));
      }
    );
  }

  private handleError(error: AxiosError): Error {
    if (error.response?.data) {
      const data = error.response.data as any;
      return new Error(data.detail || data.message || MESSAGES.SERVER_ERROR);
    }
    
    if (error.code === 'ECONNABORTED') {
      return new Error('Timeout da requisição');
    }
    
    if (error.request) {
      return new Error(MESSAGES.NETWORK_ERROR);
    }
    
    return new Error(error.message || MESSAGES.SERVER_ERROR);
  }

  // Authentication endpoints
  async login(credentials: LoginRequest): Promise<LoginResponse> {
    const response: AxiosResponse<LoginResponse> = await this.api.post(
      '/auth/login',
      credentials
    );
    
    // Store token in localStorage
    localStorage.setItem(AUTH_TOKEN_KEY, response.data.data.token);
    
    return response.data;
  }

  async getCurrentUser(): Promise<User> {
    const response: AxiosResponse<User> = await this.api.get('/auth/me');
    return response.data;
  }

  logout(): void {
    localStorage.removeItem(AUTH_TOKEN_KEY);
  }

  // Songs endpoints
  async getTopFiveSongs(): Promise<Song[]> {
    const response: AxiosResponse<{success: boolean, data: Song[]}> = await this.api.get('/songs/top-five');
    return response.data.data;
  }

  async getSongs(page = 1, filters?: SongFilters): Promise<PaginatedResponse<Song>> {
    const params = new URLSearchParams({
      page: page.toString(),
    });

    if (filters?.search) {
      params.append('search', filters.search);
    }

    if (filters?.is_active !== undefined) {
      params.append('is_active', filters.is_active.toString());
    }

    const response: AxiosResponse<{success: boolean, data: Song[], meta: any}> = await this.api.get(
      `/songs?${params.toString()}`
    );
    
    // Transform backend format to frontend format
    return {
      items: response.data.data,
      total: response.data.meta.total,
      page: response.data.meta.current_page,
      per_page: response.data.meta.per_page,
      pages: response.data.meta.last_page
    };
  }

  async getSong(id: string): Promise<Song> {
    const response: AxiosResponse<Song> = await this.api.get(`/songs/${id}`);
    return response.data;
  }

  async createSong(song: CreateSongRequest): Promise<Song> {
    const response: AxiosResponse<Song> = await this.api.post('/songs', song);
    return response.data;
  }

  async updateSong(id: string, song: UpdateSongRequest): Promise<Song> {
    const response: AxiosResponse<Song> = await this.api.put(`/songs/${id}`, song);
    return response.data;
  }

  async deleteSong(id: string): Promise<void> {
    await this.api.delete(`/songs/${id}`);
  }

  async incrementPlayCount(id: string): Promise<void> {
    await this.api.post(`/songs/${id}/play`);
  }

  async reorderSongs(songIds: string[]): Promise<void> {
    await this.api.post('/songs/reorder', { song_ids: songIds });
  }

  // Suggestions endpoints
  async getSuggestions(page = 1, filters?: SuggestionFilters): Promise<PaginatedResponse<SongSuggestion>> {
    const params = new URLSearchParams({
      page: page.toString(),
    });

    if (filters?.status) {
      params.append('status', filters.status);
    }

    if (filters?.search) {
      params.append('search', filters.search);
    }

    const response: AxiosResponse<{success: boolean, data: SongSuggestion[], meta: any}> = await this.api.get(
      `/suggestions?${params.toString()}`
    );
    
    // Transform backend format to frontend format
    return {
      items: response.data.data,
      total: response.data.meta.total,
      page: response.data.meta.current_page,
      per_page: response.data.meta.per_page,
      pages: response.data.meta.last_page
    };
  }

  async createSuggestion(suggestion: CreateSuggestionRequest): Promise<SongSuggestion> {
    const response: AxiosResponse<SongSuggestion> = await this.api.post('/suggestions', suggestion);
    return response.data;
  }

  async updateSuggestion(id: string, update: UpdateSuggestionRequest): Promise<SongSuggestion> {
    const response: AxiosResponse<{success: boolean, data: any}> = await this.api.put(`/suggestions/${id}`, update);
    
    // Handle different response structures based on action
    if (response.data.data.suggestion) {
      // Approve action returns {suggestion: {...}, song: {...}}
      return response.data.data.suggestion;
    } else {
      // Reject action returns the suggestion directly
      return response.data.data;
    }
  }

  async deleteSuggestion(id: string): Promise<void> {
    await this.api.delete(`/suggestions/${id}`);
  }

  // Utility methods
  getAuthToken(): string | null {
    return localStorage.getItem(AUTH_TOKEN_KEY);
  }

  isAuthenticated(): boolean {
    return !!this.getAuthToken();
  }
}

export const apiService = new ApiService();
export default apiService;