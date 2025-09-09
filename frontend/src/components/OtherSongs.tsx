import React, { useEffect, useState } from 'react';
import { Search, ChevronLeft, ChevronRight, Music } from 'lucide-react';
import SongCard from './SongCard';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { LoadingCard } from '@/components/ui/loading';
import { useSongs } from '@/hooks/useSongs';
import { Song, SongFilters } from '@/types';
import { debounce } from '@/lib/utils';

interface OtherSongsProps {
  onSongPlay?: (song: Song) => void;
}

export const OtherSongs: React.FC<OtherSongsProps> = ({ onSongPlay }) => {
  const { songs, loading, error, pagination, actions } = useSongs();
  const [searchTerm, setSearchTerm] = useState('');
  const [filters, setFilters] = useState<SongFilters>({});

  // Debounced search function
  const debouncedSearch = debounce((term: string) => {
    const newFilters: SongFilters = {
      ...filters,
      search: term || undefined,
    };
    setFilters(newFilters);
    actions.fetchSongs(1, newFilters);
  }, 500);

  useEffect(() => {
    // Fetch other songs (excluding top 5, so start from page 1 with position > 5)
    actions.fetchSongs(1, { ...filters });
  }, []);

  useEffect(() => {
    if (searchTerm !== '') {
      debouncedSearch(searchTerm);
    }
  }, [searchTerm]);

  const handleSongPlay = (song: Song) => {
    // Increment play count
    actions.incrementPlayCount(song.id);
    
    // Call parent handler if provided
    if (onSongPlay) {
      onSongPlay(song);
    } else {
      // Default behavior: open YouTube
      window.open(song.youtube_url, '_blank');
    }
  };

  const handlePageChange = (newPage: number) => {
    if (newPage >= 1 && newPage <= pagination.pages) {
      actions.fetchSongs(newPage, filters);
    }
  };

  const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearchTerm(e.target.value);
  };

  // Filter out top 5 songs (positions 1-5) - ensure songs is defined
  const otherSongs = (songs || []).filter(song => song.position > 5 || !song.position);

  if (error) {
    return (
      <section className="py-12">
        <div className="container mx-auto px-4">
          <div className="text-center">
            <p className="text-destructive">
              Erro ao carregar outras músicas: {error}
            </p>
          </div>
        </div>
      </section>
    );
  }

  return (
    <section className="py-12">
      <div className="container mx-auto px-4">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="flex items-center justify-center gap-2 mb-4">
            <Music className="h-6 w-6 text-primary" />
            <h2 className="text-3xl font-bold text-foreground">
              Outras Músicas
            </h2>
          </div>
          <p className="text-muted-foreground max-w-2xl mx-auto">
            Explore mais interpretações marcantes de Tião Carreiro, 
            organizadas por popularidade e carinho dos fãs.
          </p>
        </div>

        {/* Search Bar */}
        <div className="max-w-md mx-auto mb-8">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
            <Input
              type="text"
              placeholder="Buscar por título ou artista..."
              value={searchTerm}
              onChange={handleSearch}
              className="pl-10"
            />
          </div>
        </div>

        {/* Songs Grid */}
        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            {Array.from({ length: 8 }).map((_, index) => (
              <LoadingCard key={index} />
            ))}
          </div>
        ) : otherSongs.length > 0 ? (
          <>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
              {otherSongs.map((song, index) => (
                <SongCard
                  key={song.id}
                  song={song}
                  rank={song.position || (pagination.page - 1) * 10 + index + 6}
                  showRank={true}
                  onPlay={handleSongPlay}
                />
              ))}
            </div>

            {/* Pagination */}
            {pagination.pages > 1 && (
              <div className="flex items-center justify-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handlePageChange(pagination.page - 1)}
                  disabled={!pagination.hasPrev || loading}
                >
                  <ChevronLeft className="h-4 w-4 mr-1" />
                  Anterior
                </Button>

                <div className="flex items-center gap-1">
                  {Array.from({ length: Math.min(5, pagination.pages) }, (_, i) => {
                    let page: number;
                    if (pagination.pages <= 5) {
                      page = i + 1;
                    } else if (pagination.page <= 3) {
                      page = i + 1;
                    } else if (pagination.page >= pagination.pages - 2) {
                      page = pagination.pages - 4 + i;
                    } else {
                      page = pagination.page - 2 + i;
                    }

                    return (
                      <Button
                        key={page}
                        variant={pagination.page === page ? "default" : "outline"}
                        size="sm"
                        onClick={() => handlePageChange(page)}
                        disabled={loading}
                        className="w-8"
                      >
                        {page}
                      </Button>
                    );
                  })}
                </div>

                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handlePageChange(pagination.page + 1)}
                  disabled={!pagination.hasNext || loading}
                >
                  Próxima
                  <ChevronRight className="h-4 w-4 ml-1" />
                </Button>
              </div>
            )}

            {/* Results info */}
            <div className="text-center mt-4 text-sm text-muted-foreground">
              Mostrando {otherSongs.length} de {pagination.total} músicas
              {searchTerm && ` para "${searchTerm}"`}
            </div>
          </>
        ) : (
          <div className="text-center py-12">
            <Music className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
            <h3 className="text-lg font-medium text-foreground mb-2">
              {searchTerm ? 'Nenhuma música encontrada' : 'Nenhuma música disponível'}
            </h3>
            <p className="text-muted-foreground">
              {searchTerm 
                ? `Não encontramos músicas para "${searchTerm}". Tente outro termo.`
                : 'Ainda não há outras músicas cadastradas além do Top 5.'
              }
            </p>
          </div>
        )}
      </div>
    </section>
  );
};

export default OtherSongs;