import React, { useState } from 'react';
import { Play, Eye, ExternalLink, Music } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Song } from '@/types';
import { formatPlayCount, getYouTubeThumbnail, extractYouTubeVideoId } from '@/lib/utils';

interface SongCardProps {
  song: Song;
  rank?: number;
  size?: 'sm' | 'md' | 'lg';
  showRank?: boolean;
  onPlay?: (song: Song) => void;
}

export const SongCard: React.FC<SongCardProps> = ({
  song,
  rank,
  size = 'md',
  showRank = false,
  onPlay,
}) => {
  const [imageError, setImageError] = useState(false);
  
  // Only use thumbnail_url if exists, otherwise show gradient
  const thumbnailUrl = song.thumbnail_url;

  const handlePlay = () => {
    if (onPlay) {
      onPlay(song);
    } else {
      window.open(song.youtube_url, '_blank');
    }
  };

  const cardSizes = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
  };

  const imageSizes = {
    sm: 'h-32',
    md: 'h-40',
    lg: 'h-48',
  };

  return (
    <Card className={`group overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 ${cardSizes[size]}`}>
      {/* Thumbnail Container */}
      <div className={`relative ${imageSizes[size]} overflow-hidden bg-muted`}>
        {thumbnailUrl && !imageError ? (
          <img 
            src={thumbnailUrl}
            alt={song.title}
            className="w-full h-full object-cover aspect-video"
            onError={() => setImageError(true)}
            loading="lazy"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 aspect-video">
            <div className="text-center text-white">
              <Music className="h-8 w-8 mx-auto mb-1 drop-shadow-lg" />
              <div className="text-xs font-medium drop-shadow">
                Tião Carreiro
              </div>
            </div>
          </div>
        )}
        
        {/* Rank Badge */}
        {showRank && rank && (
          <div className="absolute top-2 left-2">
            <Badge 
              variant={rank <= 5 ? 'default' : 'secondary'}
              className="text-sm font-bold"
            >
              #{rank}
            </Badge>
          </div>
        )}

        {/* Play Button Overlay */}
        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-center justify-center">
          <Button
            size="icon"
            onClick={handlePlay}
            className="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/90 text-black hover:bg-white"
          >
            <Play className="h-6 w-6 ml-0.5" fill="currentColor" />
          </Button>
        </div>

        {/* Play Count */}
        {(song.plays_count || 0) > 0 && (
          <div className="absolute bottom-2 right-2">
            <Badge variant="secondary" className="text-xs bg-black/70 text-white border-0">
              <Eye className="h-3 w-3 mr-1" />
              {formatPlayCount(song.plays_count || 0)}
            </Badge>
          </div>
        )}
      </div>

      <CardContent className="p-4">
        <div className="space-y-2">
          {/* Title */}
          <h3 className="font-semibold text-base leading-tight line-clamp-2 text-foreground">
            {song.title}
          </h3>

          {/* Artist */}
          <p className="text-sm text-muted-foreground font-medium">
            {song.artist}
          </p>

          {/* Actions */}
          <div className="flex items-center justify-between pt-2">
            <Button
              variant="ghost"
              size="sm"
              onClick={handlePlay}
              className="gap-2 text-primary hover:text-primary/80"
            >
              <Play className="h-4 w-4" />
              Ouvir
            </Button>

            <Button
              variant="ghost"
              size="sm"
              asChild
            >
              <a
                href={song.youtube_url}
                target="_blank"
                rel="noopener noreferrer"
                className="gap-2"
              >
                <ExternalLink className="h-4 w-4" />
                YouTube
              </a>
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};

export default SongCard;