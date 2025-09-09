import React, { useEffect, useState, useCallback } from 'react';
import { 
  Check, 
  X, 
  Eye, 
  ExternalLink, 
  Search, 
  Filter,
  ChevronLeft,
  ChevronRight,
  Music,
  MessageSquare
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/use-toast';
import { useSuggestions } from '@/hooks/useSuggestions';
import { SongSuggestion, SuggestionFilters } from '@/types';
import { formatDateTime, debounce, getYouTubeThumbnail } from '@/lib/utils';
import { SUGGESTION_STATUS_LABELS, MESSAGES } from '@/constants';
import { LoadingTable } from '@/components/ui/loading';

export const AdminSuggestions: React.FC = () => {
  const { toast } = useToast();
  const { suggestions, loading, pagination, actions } = useSuggestions();
  
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<'all' | 'pending' | 'approved' | 'rejected'>('all');
  const [selectedSuggestion, setSelectedSuggestion] = useState<SongSuggestion | null>(null);
  const [viewDialogOpen, setViewDialogOpen] = useState(false);
  const [imageError, setImageError] = useState(false);

  // Debounced search function
  const debouncedSearch = useCallback(
    debounce((term: string, status: string) => {
      const filters: SuggestionFilters = {
        search: term || undefined,
        status: status === 'all' ? undefined : status as any,
      };
      actions.fetchSuggestions(1, filters);
    }, 500),
    []
  );

  useEffect(() => {
    actions.fetchSuggestions(1);
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    debouncedSearch(searchTerm, statusFilter);
  }, [searchTerm, statusFilter, debouncedSearch]);

  const handleApprove = async (suggestion: SongSuggestion) => {
    try {
      await actions.updateSuggestion(suggestion.id, { 
        status: 'approve',
        admin_notes: 'Sugestão aprovada automaticamente'
      });
      
      toast({
        title: "Sugestão aprovada!",
        description: `"${suggestion.title}" foi aprovada com sucesso e adicionada ao catálogo.`,
        variant: "success",
      });

      // Refresh the list to show updated status with current filters
      const filters: SuggestionFilters = {
        search: searchTerm || undefined,
        status: statusFilter === 'all' ? undefined : statusFilter as any,
      };
      actions.fetchSuggestions(pagination.page, filters);
    } catch (error) {
      toast({
        title: "Erro ao aprovar sugestão",
        description: error instanceof Error ? error.message : MESSAGES.SERVER_ERROR,
        variant: "destructive",
      });
    }
  };

  const handleReject = async (suggestion: SongSuggestion) => {
    try {
      await actions.updateSuggestion(suggestion.id, { 
        status: 'reject',
        admin_notes: 'Sugestão rejeitada'
      });
      
      toast({
        title: "Sugestão rejeitada",
        description: `"${suggestion.title}" foi rejeitada.`,
        variant: "success",
      });

      // Refresh the list to show updated status with current filters
      const filters: SuggestionFilters = {
        search: searchTerm || undefined,
        status: statusFilter === 'all' ? undefined : statusFilter as any,
      };
      actions.fetchSuggestions(pagination.page, filters);
    } catch (error) {
      toast({
        title: "Erro ao rejeitar sugestão",
        description: error instanceof Error ? error.message : MESSAGES.SERVER_ERROR,
        variant: "destructive",
      });
    }
  };

  const handleView = (suggestion: SongSuggestion) => {
    setSelectedSuggestion(suggestion);
    setImageError(false); // Reset error state when opening new dialog
    setViewDialogOpen(true);
  };

  const handlePageChange = (newPage: number) => {
    if (newPage >= 1 && newPage <= pagination.pages) {
      const filters: SuggestionFilters = {
        search: searchTerm || undefined,
        status: statusFilter === 'all' ? undefined : statusFilter as any,
      };
      actions.fetchSuggestions(newPage, filters);
    }
  };

  const getStatusVariant = (status: string) => {
    switch (status) {
      case 'approved': return 'success';
      case 'rejected': return 'destructive';
      default: return 'warning';
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-foreground">Sugestões</h1>
        <p className="text-muted-foreground mt-2">
          Gerencie as sugestões enviadas pelos usuários
        </p>
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
                placeholder="Buscar por título, artista..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
            
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value as any)}
              className="px-3 py-2 border border-input rounded-md bg-background text-foreground"
            >
              <option value="all">Todos os status</option>
              <option value="pending">Pendentes</option>
              <option value="approved">Aprovadas</option>
              <option value="rejected">Rejeitadas</option>
            </select>
          </div>
        </CardContent>
      </Card>

      {/* Suggestions Table */}
      <Card>
        <CardHeader>
          <CardTitle>Lista de Sugestões</CardTitle>
          <CardDescription>
            Total: {pagination.total} sugestões
          </CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <LoadingTable rows={5} columns={6} />
          ) : suggestions.length > 0 ? (
            <>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Música</TableHead>
                    <TableHead>Artista</TableHead>
                    <TableHead>Sugerido por</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Data</TableHead>
                    <TableHead>Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {(suggestions || []).map((suggestion) => (
                    <TableRow key={suggestion.id}>
                      <TableCell className="font-medium">
                        {suggestion.title}
                      </TableCell>
                      <TableCell>
                        {suggestion.artist}
                      </TableCell>
                      <TableCell>
                        {suggestion.suggested_by_name || 'Anônimo'}
                        {suggestion.suggested_by_email && (
                          <div className="text-xs text-muted-foreground">
                            {suggestion.suggested_by_email}
                          </div>
                        )}
                      </TableCell>
                      <TableCell>
                        <Badge variant={getStatusVariant(suggestion.status)}>
                          {SUGGESTION_STATUS_LABELS[suggestion.status]}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        {formatDateTime(suggestion.created_at)}
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handleView(suggestion)}
                          >
                            <Eye className="h-4 w-4" />
                          </Button>
                          
                          {suggestion.status === 'pending' && (
                            <>
                              <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => handleApprove(suggestion)}
                                className="text-green-600 hover:text-green-700"
                              >
                                <Check className="h-4 w-4" />
                              </Button>
                              <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => handleReject(suggestion)}
                                className="text-red-600 hover:text-red-700"
                              >
                                <X className="h-4 w-4" />
                              </Button>
                            </>
                          )}
                          
                          <Button
                            variant="ghost"
                            size="sm"
                            asChild
                          >
                            <a
                              href={suggestion.youtube_url}
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
              <MessageSquare className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-lg font-medium text-foreground mb-2">
                Nenhuma sugestão encontrada
              </h3>
              <p className="text-muted-foreground">
                {searchTerm || statusFilter !== 'all' 
                  ? 'Tente ajustar os filtros de busca.'
                  : 'Ainda não há sugestões enviadas pelos usuários.'
                }
              </p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* View Suggestion Dialog */}
      <Dialog open={viewDialogOpen} onOpenChange={setViewDialogOpen}>
        <DialogContent className="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Detalhes da Sugestão</DialogTitle>
            <DialogDescription>
              Informações completas sobre a sugestão
            </DialogDescription>
          </DialogHeader>

          {selectedSuggestion && (
            <div className="space-y-6">
              {/* YouTube Thumbnail */}
              <div className="aspect-video w-full h-48 overflow-hidden rounded-lg bg-muted">
                {selectedSuggestion.thumbnail_url && !imageError ? (
                  <img 
                    src={selectedSuggestion.thumbnail_url}
                    alt={selectedSuggestion.title}
                    className="w-full h-full object-cover"
                    onError={() => setImageError(true)}
                    loading="lazy"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500">
                    <div className="text-center text-white">
                      <Music className="h-12 w-12 mx-auto mb-2 drop-shadow-lg" />
                      <div className="text-sm font-medium drop-shadow">
                        Sugestão Musical
                      </div>
                    </div>
                  </div>
                )}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Título
                  </label>
                  <p className="mt-1 font-medium">{selectedSuggestion.title}</p>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Artista
                  </label>
                  <p className="mt-1">{selectedSuggestion.artist}</p>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Status
                  </label>
                  <div className="mt-1">
                    <Badge variant={getStatusVariant(selectedSuggestion.status)}>
                      {SUGGESTION_STATUS_LABELS[selectedSuggestion.status]}
                    </Badge>
                  </div>
                </div>
                
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Data da sugestão
                  </label>
                  <p className="mt-1">{formatDateTime(selectedSuggestion.created_at)}</p>
                </div>
                
                {selectedSuggestion.suggested_by_name && (
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">
                      Sugerido por
                    </label>
                    <p className="mt-1">{selectedSuggestion.suggested_by_name}</p>
                  </div>
                )}
                
                {selectedSuggestion.suggested_by_email && (
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">
                      E-mail
                    </label>
                    <p className="mt-1">{selectedSuggestion.suggested_by_email}</p>
                  </div>
                )}
              </div>

              <div>
                <label className="text-sm font-medium text-muted-foreground">
                  Link do YouTube
                </label>
                <div className="mt-1 flex items-center gap-2">
                  <Input 
                    value={selectedSuggestion.youtube_url}
                    readOnly
                    className="flex-1"
                  />
                  <Button
                    variant="outline"
                    size="sm"
                    asChild
                  >
                    <a
                      href={selectedSuggestion.youtube_url}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ExternalLink className="h-4 w-4" />
                    </a>
                  </Button>
                </div>
              </div>

              {selectedSuggestion.admin_notes && (
                <div>
                  <label className="text-sm font-medium text-muted-foreground">
                    Notas do administrador
                  </label>
                  <p className="mt-1 text-sm bg-muted p-3 rounded">
                    {selectedSuggestion.admin_notes}
                  </p>
                </div>
              )}

              {selectedSuggestion.status === 'pending' && (
                <div className="flex gap-3 pt-4">
                  <Button
                    onClick={() => {
                      handleApprove(selectedSuggestion);
                      setViewDialogOpen(false);
                    }}
                    className="flex-1"
                  >
                    <Check className="h-4 w-4 mr-2" />
                    Aprovar
                  </Button>
                  <Button
                    variant="destructive"
                    onClick={() => {
                      handleReject(selectedSuggestion);
                      setViewDialogOpen(false);
                    }}
                    className="flex-1"
                  >
                    <X className="h-4 w-4 mr-2" />
                    Rejeitar
                  </Button>
                </div>
              )}
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default AdminSuggestions;