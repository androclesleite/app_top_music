import { useState, useCallback } from 'react';
import { SongSuggestion, PaginatedResponse, CreateSuggestionRequest, UpdateSuggestionRequest, SuggestionFilters } from '@/types';
import { apiService } from '@/services/api';

interface UseSuggestionsResult {
  suggestions: SongSuggestion[];
  loading: boolean;
  error: string | null;
  pagination: {
    page: number;
    total: number;
    pages: number;
    hasNext: boolean;
    hasPrev: boolean;
  };
  actions: {
    fetchSuggestions: (page?: number, filters?: SuggestionFilters) => Promise<void>;
    createSuggestion: (suggestion: CreateSuggestionRequest) => Promise<SongSuggestion>;
    updateSuggestion: (id: string, update: UpdateSuggestionRequest) => Promise<SongSuggestion>;
    deleteSuggestion: (id: string) => Promise<void>;
    refresh: () => Promise<void>;
  };
}

export function useSuggestions(): UseSuggestionsResult {
  const [suggestions, setSuggestions] = useState<SongSuggestion[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pagination, setPagination] = useState({
    page: 1,
    total: 0,
    pages: 0,
    hasNext: false,
    hasPrev: false,
  });

  const fetchSuggestions = useCallback(async (page = 1, filters?: SuggestionFilters) => {
    setLoading(true);
    setError(null);

    try {
      const response: PaginatedResponse<SongSuggestion> = await apiService.getSuggestions(page, filters);
      setSuggestions(response.items);
      setPagination({
        page: response.page,
        total: response.total,
        pages: response.pages,
        hasNext: response.page < response.pages,
        hasPrev: response.page > 1,
      });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao carregar sugestões');
      setSuggestions([]);
    } finally {
      setLoading(false);
    }
  }, []);

  const createSuggestion = useCallback(async (suggestion: CreateSuggestionRequest): Promise<SongSuggestion> => {
    setError(null);
    
    try {
      const newSuggestion = await apiService.createSuggestion(suggestion);
      
      // Add to local state if it's the first page
      if (pagination.page === 1) {
        setSuggestions(prev => [newSuggestion, ...prev]);
      }
      
      return newSuggestion;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao criar sugestão';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  }, [pagination.page]);

  const updateSuggestion = useCallback(async (id: string, update: UpdateSuggestionRequest): Promise<SongSuggestion> => {
    setError(null);
    
    try {
      const updatedSuggestion = await apiService.updateSuggestion(id, update);
      
      // Update local state
      setSuggestions(prev => prev.map(s => s.id === id ? updatedSuggestion : s));
      
      return updatedSuggestion;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao atualizar sugestão';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  }, []);

  const deleteSuggestion = useCallback(async (id: string): Promise<void> => {
    setError(null);
    
    try {
      await apiService.deleteSuggestion(id);
      
      // Update local state
      setSuggestions(prev => prev.filter(s => s.id !== id));
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao excluir sugestão';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  }, []);

  const refresh = useCallback(async (): Promise<void> => {
    setLoading(true);
    setError(null);

    try {
      const currentPage = pagination.page;
      const response: PaginatedResponse<SongSuggestion> = await apiService.getSuggestions(currentPage);
      setSuggestions(response.items);
      setPagination({
        page: response.page,
        total: response.total,
        pages: response.pages,
        hasNext: response.page < response.pages,
        hasPrev: response.page > 1,
      });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao carregar sugestões');
      setSuggestions([]);
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    suggestions,
    loading,
    error,
    pagination,
    actions: {
      fetchSuggestions,
      createSuggestion,
      updateSuggestion,
      deleteSuggestion,
      refresh,
    },
  };
}