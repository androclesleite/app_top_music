import React, { useEffect } from 'react';
import { Trophy, Star } from 'lucide-react';
import SongCard from './SongCard';
import { LoadingCard } from '@/components/ui/loading';
import { useSongs } from '@/hooks/useSongs';
import { Song } from '@/types';

interface TopFiveSongsProps {
  onSongPlay?: (song: Song) => void;
}

export const TopFiveSongs: React.FC<TopFiveSongsProps> = ({ onSongPlay }) => {
  const { topFiveSongs, loading, error, actions } = useSongs();

  useEffect(() => {
    actions.fetchTopFive();
  }, []);

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

  if (error) {
    return (
      <section className="py-12">
        <div className="container mx-auto px-4">
          <div className="text-center">
            <p className="text-destructive">
              Erro ao carregar o Top 5: {error}
            </p>
          </div>
        </div>
      </section>
    );
  }

  return (
    <section className="py-12 bg-gradient-to-br from-primary/5 via-background to-accent/5">
      <div className="container mx-auto px-4">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="flex items-center justify-center gap-3 mb-4">
            <Trophy className="h-8 w-8 text-yellow-500" />
            <h2 className="text-4xl font-bold text-foreground">
              Top 5 Tião Carreiro
            </h2>
            <Trophy className="h-8 w-8 text-yellow-500" />
          </div>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            As músicas mais queridas e reproduzidas do rei do violão, 
            selecionadas pela comunidade de fãs da música sertaneja raiz.
          </p>
        </div>

        {/* Top 5 Grid */}
        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            {Array.from({ length: 5 }).map((_, index) => (
              <LoadingCard key={index} />
            ))}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            {(topFiveSongs || []).map((song, index) => {
              const rank = index + 1;
              return (
                <div key={song.id} className="relative">
                  {/* Special styling for #1 */}
                  {rank === 1 && (
                    <div className="absolute -top-4 -left-2 -right-2 -bottom-2 bg-gradient-to-r from-yellow-400/20 via-yellow-500/20 to-yellow-400/20 rounded-xl -z-10 animate-pulse" />
                  )}
                  
                  {/* Position number */}
                  <div className="absolute -top-3 -left-3 z-10">
                    <div className={`
                      flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm
                      ${rank === 1 ? 'bg-yellow-500 text-white shadow-lg' :
                        rank === 2 ? 'bg-gray-400 text-white shadow-md' :
                        rank === 3 ? 'bg-amber-600 text-white shadow-md' :
                        'bg-primary text-primary-foreground shadow-sm'}
                    `}>
                      {rank === 1 && <Star className="h-4 w-4 fill-current" />}
                      {rank !== 1 && rank}
                    </div>
                  </div>

                  <SongCard
                    song={song}
                    rank={rank}
                    size="md"
                    showRank={false} // We're showing custom rank above
                    onPlay={handleSongPlay}
                  />
                </div>
              );
            })}

            {/* Empty slots if less than 5 songs */}
            {(topFiveSongs || []).length < 5 && 
              Array.from({ length: 5 - (topFiveSongs || []).length }).map((_, index) => (
                <div 
                  key={`empty-${index}`} 
                  className="border-2 border-dashed border-muted rounded-lg h-64 flex items-center justify-center bg-muted/20"
                >
                  <div className="text-center text-muted-foreground">
                    <div className="text-2xl font-bold mb-2">
                      #{topFiveSongs.length + index + 1}
                    </div>
                    <p className="text-sm">
                      Aguardando música
                    </p>
                  </div>
                </div>
              ))
            }
          </div>
        )}

        {/* Call to action */}
        <div className="text-center mt-12">
          <p className="text-muted-foreground mb-4">
            Não encontrou sua música favorita do Tião Carreiro no Top 5?
          </p>
          <a 
            href="#suggest-song" 
            className="text-primary font-medium hover:underline"
          >
            Envie sua sugestão aqui embaixo! 👇
          </a>
        </div>
      </div>
    </section>
  );
};

export default TopFiveSongs;