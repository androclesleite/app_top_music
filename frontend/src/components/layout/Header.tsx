import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Music, LogOut, Settings } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useAuth } from '@/hooks/useAuth';
import { ROUTES } from '@/constants';

export const Header: React.FC = () => {
  const { user, isAuthenticated, logout } = useAuth();
  const location = useLocation();

  const handleLogout = () => {
    logout();
  };

  const isActiveRoute = (path: string) => {
    return location.pathname === path;
  };

  return (
    <header className="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container mx-auto px-4">
        <div className="flex h-16 items-center justify-between">
          {/* Logo */}
          <Link 
            to={ROUTES.HOME} 
            className="flex items-center gap-2 text-xl font-bold text-primary hover:opacity-80 transition-opacity"
          >
            <Music className="h-8 w-8" />
            <span className="hidden sm:inline">Top 5 Tião Carreiro</span>
            <span className="sm:hidden">T5TC</span>
          </Link>

          {/* Navigation */}
          <nav className="hidden md:flex items-center gap-6">
            <Link
              to={ROUTES.HOME}
              className={`text-sm font-medium transition-colors hover:text-primary ${
                isActiveRoute(ROUTES.HOME) 
                  ? 'text-primary' 
                  : 'text-muted-foreground'
              }`}
            >
              Início
            </Link>
            
            {isAuthenticated && (
              <>
                <Link
                  to={ROUTES.ADMIN}
                  className={`text-sm font-medium transition-colors hover:text-primary ${
                    location.pathname.startsWith('/admin')
                      ? 'text-primary' 
                      : 'text-muted-foreground'
                  }`}
                >
                  Administração
                </Link>
                <Link
                  to={ROUTES.ADMIN_SONGS}
                  className={`text-sm font-medium transition-colors hover:text-primary ${
                    isActiveRoute(ROUTES.ADMIN_SONGS)
                      ? 'text-primary' 
                      : 'text-muted-foreground'
                  }`}
                >
                  Músicas
                </Link>
                <Link
                  to={ROUTES.ADMIN_SUGGESTIONS}
                  className={`text-sm font-medium transition-colors hover:text-primary ${
                    isActiveRoute(ROUTES.ADMIN_SUGGESTIONS)
                      ? 'text-primary' 
                      : 'text-muted-foreground'
                  }`}
                >
                  Sugestões
                </Link>
              </>
            )}
          </nav>

          {/* User Actions */}
          <div className="flex items-center gap-2">
            {isAuthenticated ? (
              <div className="flex items-center gap-3">
                <span className="hidden sm:inline text-sm text-muted-foreground">
                  Olá, {user?.username}
                </span>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={handleLogout}
                  className="gap-2"
                >
                  <LogOut className="h-4 w-4" />
                  <span className="hidden sm:inline">Sair</span>
                </Button>
              </div>
            ) : (
              <Button asChild variant="default" size="sm">
                <Link to={ROUTES.LOGIN}>
                  <Settings className="h-4 w-4 mr-2" />
                  Admin
                </Link>
              </Button>
            )}
          </div>
        </div>

        {/* Mobile Navigation */}
        <div className="md:hidden pb-3">
          <nav className="flex items-center gap-4 overflow-x-auto">
            <Link
              to={ROUTES.HOME}
              className={`whitespace-nowrap text-sm font-medium transition-colors hover:text-primary ${
                isActiveRoute(ROUTES.HOME) 
                  ? 'text-primary' 
                  : 'text-muted-foreground'
              }`}
            >
              Início
            </Link>
            
            {isAuthenticated && (
              <>
                <Link
                  to={ROUTES.ADMIN}
                  className={`whitespace-nowrap text-sm font-medium transition-colors hover:text-primary ${
                    location.pathname.startsWith('/admin')
                      ? 'text-primary' 
                      : 'text-muted-foreground'
                  }`}
                >
                  Admin
                </Link>
                <Link
                  to={ROUTES.ADMIN_SONGS}
                  className={`whitespace-nowrap text-sm font-medium transition-colors hover:text-primary ${
                    isActiveRoute(ROUTES.ADMIN_SONGS)
                      ? 'text-primary' 
                      : 'text-muted-foreground'
                  }`}
                >
                  Músicas
                </Link>
                <Link
                  to={ROUTES.ADMIN_SUGGESTIONS}
                  className={`whitespace-nowrap text-sm font-medium transition-colors hover:text-primary ${
                    isActiveRoute(ROUTES.ADMIN_SUGGESTIONS)
                      ? 'text-primary' 
                      : 'text-muted-foreground'
                  }`}
                >
                  Sugestões
                </Link>
              </>
            )}
          </nav>
        </div>
      </div>
    </header>
  );
};