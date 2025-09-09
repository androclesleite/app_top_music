import React, { useEffect, useState, useCallback } from 'react';
import { 
  Plus, 
  Edit, 
  Trash2, 
  Eye, 
  ExternalLink, 
  Search, 
  Filter,
  ChevronLeft,
  ChevronRight,
  Music,
  ArrowUpDown
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/use-toast';
import { useSongs } from '@/hooks/useSongs';
import { Song, SongFilters, CreateSongRequest, UpdateSongRequest } from '@/types';
import { formatDateTime, formatPlayCount, debounce, getYouTubeThumbnail } from '@/lib/utils';
import { MESSAGES } from '@/constants';
import { LoadingTable } from '@/components/ui/loading';
import AdminSongForm from './AdminSongForm';

export const SongManagement: React.FC = () => {
  const { toast } = useToast();
  const { songs, topFiveSongs, loading, pagination, actions } = useSongs();
  
  const [searchTerm, setSearchTerm] = useState('');
  const [activeFilter, setActiveFilter] = useState<'all' | 'active' | 'inactive'>('all');
  const [selectedSong, setSelectedSong] = useState<Song | null>(null);
  const [viewDialogOpen, setViewDialogOpen] = useState(false);
  const [formDialogOpen, setFormDialogOpen] = useState(false);
  const [songToEdit, setSongToEdit] = useState<Song | null>(null);

  // Combine top five and other songs for complete list
  const allSongs = [...(topFiveSongs || []), ...(songs || [])];

  // Debounced search function
  const debouncedSearch = useCallback(
    debounce((term: string, active: string) => {
      const filters: SongFilters = {
        search: term || undefined,
        is_active: active === 'all' ? undefined : active === 'active',
      };
      actions.fetchSongs(1, filters);
    }, 500),
    []
  );

  useEffect(() => {
    actions.fetchSongs(1);
    actions.fetchTopFive();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    debouncedSearch(searchTerm, activeFilter);
  }, [searchTerm, activeFilter, debouncedSearch]);

  const handleCreate = () => {
    setSongToEdit(null);
    setFormDialogOpen(true);
  };

  const handleEdit = (song: Song) => {
    setSongToEdit(song);
    setFormDialogOpen(true);
  };

  const handleView = (song: Song) => {
    setSelectedSong(song);
    setViewDialogOpen(true);
  };

  const handleDelete = async (song: Song) => {
    if (!confirm(`Tem certeza que deseja excluir "${song.title}"?`)) {
      return;
    }

    try {
      await actions.deleteSong(song.id);
      
      toast({
        title: "Música excluída!",
        description: `"${song.title}" foi removida com sucesso.`,
        variant: "success",
      });
    } catch (error) {
      toast({
        title: "Erro ao excluir música",
        description: error instanceof Error ? error.message : MESSAGES.SERVER_ERROR,
        variant: "destructive",
      });
    }
  };

  const handleSave = async (data: CreateSongRequest | UpdateSongRequest) => {
    if (songToEdit) {
      await actions.updateSong(songToEdit.id, data as UpdateSongRequest);
    } else {
      await actions.createSong(data as CreateSongRequest);
    }
  };

  const handlePageChange = (newPage: number) => {
    if (newPage >= 1 && newPage <= pagination.pages) {
      const filters: SongFilters = {
        search: searchTerm || undefined,
        is_active: activeFilter === 'all' ? undefined : activeFilter === 'active',
      };
      actions.fetchSongs(newPage, filters);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-foreground">Gerenciar Músicas</h1>
          <p className="text-muted-foreground mt-2">
            Adicione, edite e organize as músicas de Tião Carreiro
          </p>
        </div>
        
        <Button onClick={handleCreate} className="gap-2">
          <Plus className="h-4 w-4" />
          Nova Música
        </Button>
      </div>

      {/* Quick Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Total</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{allSongs.length}</div>
            <p className="text-xs text-muted-foreground">músicas</p>
          </CardContent>
        </Card>
        
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Top 5</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{topFiveSongs.length}</div>
            <p className="text-xs text-muted-foreground">principais</p>
          </CardContent>
        </Card>
        
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Outras</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{songs.length}</div>
            <p className="text-xs text-muted-foreground">músicas</p>
          </CardContent>
        </Card>
        
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Total de Plays</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {formatPlayCount(allSongs.reduce((sum, song) => sum + (song.plays_count || 0), 0))}
            </div>
            <p className="text-xs text-muted-foreground">reproduções</p>
          </CardContent>
        </Card>
      </div>

      {/* Filters */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Filter className="h-5 w-5" />
            Filtros
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Buscar por título ou artista..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
            
            <select
              value={activeFilter}
              onChange={(e) => setActiveFilter(e.target.value as any)}
              className="px-3 py-2 border border-input rounded-md bg-background text-foreground"
            >
              <option value="all">Todas</option>
              <option value="active">Ativas</option>
              <option value="inactive">Inativas</option>
            </select>
          </div>
        </CardContent>
      </Card>

      {/* Songs Table */}
      <Card>
        <CardHeader>
          <CardTitle>Lista de Músicas</CardTitle>
          <CardDescription>
            Total: {pagination.total} músicas
          </CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <LoadingTable rows={10} columns={7} />
          ) : allSongs.length > 0 ? (
            <>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>
                      <Button variant="ghost" size="sm" className="h-auto p-0 font-medium">
                        Posição
                        <ArrowUpDown className="ml-1 h-3 w-3" />
                      </Button>
                    </TableHead>
                    <TableHead>Música</TableHead>
                    <TableHead>Artista</TableHead>
                    <TableHead>Reproduções</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Atualizado</TableHead>
                    <TableHead>Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {allSongs.map((song) => (
                    <TableRow key={song.id}>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                            song.is_top_five
                              ? 'bg-primary text-primary-foreground' 
                              : 'bg-muted text-muted-foreground'
                          }`}>
                            #{song.position || '-'}
                          </div>
                          {song.is_top_five && (
                            <Badge variant="default" className="text-xs">
                              Top 5
                            </Badge>
                          )}
                        </div>
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-3">
                          <div className="relative w-12 h-8 rounded overflow-hidden bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                            <Music className="h-3 w-3 text-white" />
                          </div>
                          <div>
                            <p className="font-medium truncate max-w-[200px]">
                              {song.title}
                            </p>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell>{song.artist}</TableCell>
                      <TableCell>
                        <Badge variant="secondary">
                          {formatPlayCount(song.plays_count || 0)}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <Badge variant={song.is_active ? 'success' : 'secondary'}>
                          {song.is_active ? 'Ativa' : 'Inativa'}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">
                        {formatDateTime(song.updated_at)}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-1">
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handleView(song)}
                          >
                            <Eye className="h-4 w-4" />
                          </Button>
                          
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handleEdit(song)}
                          >
                            <Edit className="h-4 w-4" />
                          </Button>
                          
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handleDelete(song)}
                            className="text-red-600 hover:text-red-700"
                          >
                            <Trash2 className="h-4 w-4" />
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
                            >
                              <ExternalLink className="h-4 w-4" />
                            </a>
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>

              {/* Pagination */}
              {pagination.pages > 1 && (
                <div className="flex items-center justify-center gap-2 mt-6">
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
            </>
          ) : (
            <div className="text-center py-12">
              <Music className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-lg font-medium text-foreground mb-2">
                Nenhuma música encontrada
              </h3>
              <p className="text-muted-foreground mb-4">
                {searchTerm || activeFilter !== 'all' 
                  ? 'Tente ajustar os filtros de busca.'
                  : 'Ainda não há músicas cadastradas.'
                }
              </p>
              <Button onClick={handleCreate}>
                <Plus className="h-4 w-4 mr-2" />
                Adicionar primeira música
              </Button>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Song Form Dialog */}
      <AdminSongForm
        song={songToEdit}
        open={formDialogOpen}
        onOpenChange={setFormDialogOpen}
        onSave={handleSave}
      />

      {/* View Song Dialog */}
      <Dialog open={viewDialogOpen} onOpenChange={setViewDialogOpen}>
        <DialogContent className="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Detalhes da Música</DialogTitle>
            <DialogDescription>
              Informações completas sobre a música
            </DialogDescription>
          </DialogHeader>

          {selectedSong && (
            <div className="space-y-6">
              {/* YouTube Thumbnail */}
              <div className="aspect-video w-full overflow-hidden rounded-lg bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center">
                <div className="text-center text-white">
                  <Music className="h-16 w-16 mx-auto mb-4 drop-shadow-lg" />
                  <div className="text-lg font-medium drop-shadow">
                    Tião Carreiro
                  </div>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Título
                  </label>
                  <p className="mt-1 font-medium">{selectedSong.title}</p>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Artista
                  </label>
                  <p className="mt-1">{selectedSong.artist}</p>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Posição
                  </label>
                  <div className="mt-1">
                    <Badge variant={selectedSong.position <= 5 ? 'default' : 'secondary'}>
                      #{selectedSong.position}
                      {selectedSong.position <= 5 && ' - Top 5'}
                    </Badge>
                  </div>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Reproduções
                  </label>
                  <p className="mt-1 font-medium">{formatPlayCount(selectedSong.plays_count || 0)}</p>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Status
                  </label>
                  <div className="mt-1">
                    <Badge variant={selectedSong.is_active ? 'success' : 'secondary'}>
                      {selectedSong.is_active ? 'Ativa' : 'Inativa'}
                    </Badge>
                  </div>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Última atualização
                  </label>
                  <p className="mt-1">{formatDateTime(selectedSong.updated_at)}</p>
                </div>
              </div>

              <div>
                <label className="text-sm font-medium text-muted-foreground">
                  Link do YouTube
                </label>
                <div className="mt-1 flex items-center gap-2">
                  <Input 
                    value={selectedSong.youtube_url}
                    readOnly
                    className="flex-1"
                  />
                  <Button
                    variant="outline"
                    size="sm"
                    asChild
                  >
                    <a
                      href={selectedSong.youtube_url}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ExternalLink className="h-4 w-4" />
                    </a>
                  </Button>
                </div>
              </div>

              <div className="flex gap-3 pt-4">
                <Button
                  onClick={() => {
                    handleEdit(selectedSong);
                    setViewDialogOpen(false);
                  }}
                  className="flex-1"
                >
                  <Edit className="h-4 w-4 mr-2" />
                  Editar
                </Button>
                <Button
                  variant="destructive"
                  onClick={() => {
                    handleDelete(selectedSong);
                    setViewDialogOpen(false);
                  }}
                  className="flex-1"
                >
                  <Trash2 className="h-4 w-4 mr-2" />
                  Excluir
                </Button>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default SongManagement;