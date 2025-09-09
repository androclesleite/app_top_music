import { useState, useEffect, useCallback } from 'react';
import { Song, PaginatedResponse, CreateSongRequest, UpdateSongRequest, SongFilters } from '@/types';
import { apiService } from '@/services/api';
import { ITEMS_PER_PAGE } from '@/constants';

interface UseSongsResult {
  songs: Song[];
  topFiveSongs: Song[];
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
    fetchSongs: (page?: number, filters?: SongFilters) => Promise<void>;
    fetchTopFive: () => Promise<void>;
    createSong: (song: CreateSongRequest) => Promise<Song>;
    updateSong: (id: string, song: UpdateSongRequest) => Promise<Song>;
    deleteSong: (id: string) => Promise<void>;
    incrementPlayCount: (id: string) => Promise<void>;
    reorderSongs: (songIds: string[]) => Promise<void>;
    refresh: () => Promise<void>;
  };
}

export function useSongs(): UseSongsResult {
  const [songs, setSongs] = useState<Song[]>([]);
  const [topFiveSongs, setTopFiveSongs] = useState<Song[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pagination, setPagination] = useState({
    page: 1,
    total: 0,
    pages: 0,
    hasNext: false,
    hasPrev: false,
  });

  const fetchSongs = useCallback(async (page = 1, filters?: SongFilters) => {
    setLoading(true);
    setError(null);

    try {
      const response: PaginatedResponse<Song> = await apiService.getSongs(page, filters);
      setSongs(response.items);
      setPagination({
        page: response.page,
        total: response.total,
        pages: response.pages,
        hasNext: response.page < response.pages,
        hasPrev: response.page > 1,
      });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao carregar músicas');
      setSongs([]);
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchTopFive = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await apiService.getTopFiveSongs();
      setTopFiveSongs(response);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao carregar top 5');
      setTopFiveSongs([]);
    } finally {
      setLoading(false);
    }
  }, []);

  const createSong = useCallback(async (song: CreateSongRequest): Promise<Song> => {
    setError(null);
    
    try {
      const newSong = await apiService.createSong(song);
      
      // Update the local state if the song is in the current page
      if (newSong.position <= 5) {
        const topFiveResponse = await apiService.getTopFiveSongs();
        setTopFiveSongs(topFiveResponse);
      }
      
      const response: PaginatedResponse<Song> = await apiService.getSongs(pagination.page);
      setSongs(response.items);
      setPagination({
        page: response.page,
        total: response.total,
        pages: response.pages,
        hasNext: response.page < response.pages,
        hasPrev: response.page > 1,
      });
      
      return newSong;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao criar música';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  }, [pagination.page]);

  const updateSong = useCallback(async (id: string, song: UpdateSongRequest): Promise<Song> => {
    setError(null);
    
    try {
      const updatedSong = await apiService.updateSong(id, song);
      
      // Update local state
      setSongs(prev => prev.map(s => s.id === id ? updatedSong : s));
      setTopFiveSongs(prev => prev.map(s => s.id === id ? updatedSong : s));
      
      return updatedSong;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao atualizar música';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  }, []);

  const deleteSong = useCallback(async (id: string): Promise<void> => {
    setError(null);
    
    try {
      await apiService.deleteSong(id);
      
      // Update local state
      setSongs(prev => prev.filter(s => s.id !== id));
      setTopFiveSongs(prev => prev.filter(s => s.id !== id));
      
      // Refresh to get updated positions
      const topFiveResponse = await apiService.getTopFiveSongs();
      setTopFiveSongs(topFiveResponse);
      
      const response: PaginatedResponse<Song> = await apiService.getSongs(pagination.page);
      setSongs(response.items);
      setPagination({
        page: response.page,
        total: response.total,
        pages: response.pages,
        hasNext: response.page < response.pages,
        hasPrev: response.page > 1,
      });
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao excluir música';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  }, [pagination.page]);

  const incrementPlayCount = useCallback(async (id: string): Promise<void> => {
    try {
      await apiService.incrementPlayCount(id);
      
      // Update local state
      setSongs(prev => prev.map(s => 
        s.id === id ? { ...s, plays_count: (s.plays_count || 0) + 1 } : s
      ));
      setTopFiveSongs(prev => prev.map(s => 
        s.id === id ? { ...s, plays_count: (s.plays_count || 0) + 1 } : s
      ));
    } catch (err) {
      // Silently fail for play count increment
      console.error('Failed to increment play count:', err);
    }
  }, []);

  const reorderSongs = useCallback(async (songIds: string[]): Promise<void> => {
    setError(null);
    
    try {
      await apiService.reorderSongs(songIds);
      
      const topFiveResponse = await apiService.getTopFiveSongs();
      setTopFiveSongs(topFiveResponse);
      
      const response: PaginatedResponse<Song> = await apiService.getSongs(pagination.page);
      setSongs(response.items);
      setPagination({
        page: response.page,
        total: response.total,
        pages: response.pages,
        hasNext: response.page < response.pages,
        hasPrev: response.page > 1,
      });
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erro ao reordenar músicas';
      setError(errorMessage);
      throw new Error(errorMessage);
    }
  }, [pagination.page]);

  const refresh = useCallback(async (): Promise<void> => {
    setLoading(true);
    setError(null);
    
    try {
      const [topFiveResponse, songsResponse] = await Promise.all([
        apiService.getTopFiveSongs(),
        apiService.getSongs(pagination.page)
      ]);
      
      setTopFiveSongs(topFiveResponse);
      setSongs(songsResponse.items);
      setPagination({
        page: songsResponse.page,
        total: songsResponse.total,
        pages: songsResponse.pages,
        hasNext: songsResponse.page < songsResponse.pages,
        hasPrev: songsResponse.page > 1,
      });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao atualizar dados');
    } finally {
      setLoading(false);
    }
  }, [pagination.page]);

  return {
    songs,
    topFiveSongs,
    loading,
    error,
    pagination,
    actions: {
      fetchSongs,
      fetchTopFive,
      createSong,
      updateSong,
      deleteSong,
      incrementPlayCount,
      reorderSongs,
      refresh,
    },
  };
}