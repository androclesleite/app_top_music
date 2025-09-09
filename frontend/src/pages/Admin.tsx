import React, { useEffect, useState } from 'react';
import { BarChart, Users, Music, MessageSquare, TrendingUp, Clock } from 'lucide-react';
import { AdminLayout } from '@/components/layout/Layout';
import { Sidebar } from '@/components/layout/Sidebar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSongs } from '@/hooks/useSongs';
import { useSuggestions } from '@/hooks/useSuggestions';
import { formatDate } from '@/lib/utils';

export const Admin: React.FC = () => {
  const { songs, topFiveSongs, actions: songActions } = useSongs();
  const { suggestions, actions: suggestionActions } = useSuggestions();
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

  useEffect(() => {
    // Load initial data
    songActions.fetchSongs();
    songActions.fetchTopFive();
    suggestionActions.fetchSuggestions(1, { status: 'pending' });
  }, []);

  // Calculate statistics - ensure arrays are defined
  const totalSongs = (songs || []).length + (topFiveSongs || []).length;
  const totalPlayCount = [...(songs || []), ...(topFiveSongs || [])].reduce((sum, song) => sum + (song.plays_count || 0), 0);
  const pendingSuggestions = (suggestions || []).filter(s => s.status === 'pending').length;
  const recentSuggestions = (suggestions || []).slice(0, 5);

  const stats = [
    {
      title: 'Total de Músicas',
      value: totalSongs.toString(),
      description: `${topFiveSongs.length} no Top 5`,
      icon: Music,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50 dark:bg-blue-900/20',
    },
    {
      title: 'Reproduções Totais',
      value: totalPlayCount.toLocaleString(),
      description: 'Todas as músicas',
      icon: TrendingUp,
      color: 'text-green-600',
      bgColor: 'bg-green-50 dark:bg-green-900/20',
    },
    {
      title: 'Sugestões Pendentes',
      value: pendingSuggestions.toString(),
      description: 'Aguardando análise',
      icon: MessageSquare,
      color: 'text-orange-600',
      bgColor: 'bg-orange-50 dark:bg-orange-900/20',
    },
    {
      title: 'Top 5 Mais Ouvida',
      value: topFiveSongs[0]?.plays_count?.toString() || '0',
      description: topFiveSongs[0]?.title || 'Nenhuma',
      icon: BarChart,
      color: 'text-purple-600',
      bgColor: 'bg-purple-50 dark:bg-purple-900/20',
    },
  ];

  return (
    <AdminLayout
      sidebar={
        <Sidebar 
          isCollapsed={sidebarCollapsed}
          onToggle={() => setSidebarCollapsed(!sidebarCollapsed)}
        />
      }
    >
      <div className="space-y-8">
        {/* Page Header */}
        <div>
          <h1 className="text-3xl font-bold text-foreground">Dashboard</h1>
          <p className="text-muted-foreground mt-2">
            Visão geral do projeto Top 5 Tião Carreiro
          </p>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {stats.map((stat, index) => {
            const Icon = stat.icon;
            return (
              <Card key={index}>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">
                    {stat.title}
                  </CardTitle>
                  <div className={`p-2 rounded-lg ${stat.bgColor}`}>
                    <Icon className={`h-4 w-4 ${stat.color}`} />
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stat.value}</div>
                  <p className="text-xs text-muted-foreground">
                    {stat.description}
                  </p>
                </CardContent>
              </Card>
            );
          })}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Top 5 Overview */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <BarChart className="h-5 w-5" />
                Top 5 Atual
              </CardTitle>
              <CardDescription>
                Músicas mais populares no ranking
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {(topFiveSongs || []).length > 0 ? (
                  (topFiveSongs || []).map((song, index) => (
                    <div key={song.id} className="flex items-center gap-3">
                      <div className="flex-shrink-0 w-8 h-8 bg-primary rounded-full flex items-center justify-center text-primary-foreground text-sm font-bold">
                        {index + 1}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium truncate">
                          {song.title}
                        </p>
                        <p className="text-xs text-muted-foreground truncate">
                          {song.artist}
                        </p>
                      </div>
                      <div className="text-right">
                        <p className="text-sm font-medium">
                          {song.plays_count?.toLocaleString() || '0'}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          reproduções
                        </p>
                      </div>
                    </div>
                  ))
                ) : (
                  <p className="text-muted-foreground text-center py-8">
                    Nenhuma música no Top 5
                  </p>
                )}
              </div>
            </CardContent>
          </Card>

          {/* Recent Suggestions */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="h-5 w-5" />
                Sugestões Recentes
              </CardTitle>
              <CardDescription>
                Últimas sugestões recebidas
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {(recentSuggestions || []).length > 0 ? (
                  (recentSuggestions || []).map((suggestion) => (
                    <div key={suggestion.id} className="flex items-start gap-3">
                      <div className={`flex-shrink-0 w-2 h-2 mt-2 rounded-full ${
                        suggestion.status === 'pending' ? 'bg-yellow-500' :
                        suggestion.status === 'approved' ? 'bg-green-500' :
                        'bg-red-500'
                      }`} />
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium truncate">
                          {suggestion.title}
                        </p>
                        <p className="text-xs text-muted-foreground truncate">
                          {suggestion.artist}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          {formatDate(suggestion.created_at)}
                        </p>
                      </div>
                      <div className="text-right">
                        <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                          suggestion.status === 'pending' 
                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300'
                            : suggestion.status === 'approved'
                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
                            : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
                        }`}>
                          {suggestion.status === 'pending' ? 'Pendente' :
                           suggestion.status === 'approved' ? 'Aprovada' : 'Rejeitada'}
                        </span>
                      </div>
                    </div>
                  ))
                ) : (
                  <p className="text-muted-foreground text-center py-8">
                    Nenhuma sugestão recente
                  </p>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Ações Rápidas</CardTitle>
            <CardDescription>
              Links para as principais funcionalidades administrativas
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <a
                href="/admin/songs"
                className="flex items-center gap-3 p-4 rounded-lg border hover:bg-accent transition-colors"
              >
                <Music className="h-5 w-5 text-primary" />
                <div>
                  <p className="font-medium">Gerenciar Músicas</p>
                  <p className="text-sm text-muted-foreground">Adicionar, editar e organizar</p>
                </div>
              </a>
              
              <a
                href="/admin/suggestions"
                className="flex items-center gap-3 p-4 rounded-lg border hover:bg-accent transition-colors"
              >
                <MessageSquare className="h-5 w-5 text-primary" />
                <div>
                  <p className="font-medium">Analisar Sugestões</p>
                  <p className="text-sm text-muted-foreground">
                    {pendingSuggestions > 0 ? `${pendingSuggestions} pendentes` : 'Nenhuma pendente'}
                  </p>
                </div>
              </a>
              
              <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-3 p-4 rounded-lg border hover:bg-accent transition-colors"
              >
                <Users className="h-5 w-5 text-primary" />
                <div>
                  <p className="font-medium">Ver Site Público</p>
                  <p className="text-sm text-muted-foreground">Como os usuários veem</p>
                </div>
              </a>
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
};

export default Admin;