import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { 
  LayoutDashboard, 
  Music, 
  MessageSquare, 
  Settings,
  ChevronLeft,
  ChevronRight
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { ROUTES } from '@/constants';
import { cn } from '@/lib/utils';

interface SidebarProps {
  isCollapsed?: boolean;
  onToggle?: () => void;
}

interface SidebarItem {
  label: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
  badge?: number;
}

export const Sidebar: React.FC<SidebarProps> = ({ 
  isCollapsed = false, 
  onToggle 
}) => {
  const location = useLocation();

  const sidebarItems: SidebarItem[] = [
    {
      label: 'Dashboard',
      href: ROUTES.ADMIN,
      icon: LayoutDashboard,
    },
    {
      label: 'Músicas',
      href: ROUTES.ADMIN_SONGS,
      icon: Music,
    },
    {
      label: 'Sugestões',
      href: ROUTES.ADMIN_SUGGESTIONS,
      icon: MessageSquare,
    },
  ];

  const isActiveRoute = (path: string) => {
    if (path === ROUTES.ADMIN) {
      return location.pathname === path;
    }
    return location.pathname.startsWith(path);
  };

  return (
    <aside 
      className={cn(
        "flex flex-col h-full bg-card border-r border-border transition-all duration-300",
        isCollapsed ? "w-16" : "w-64"
      )}
    >
      {/* Header */}
      <div className="flex items-center justify-between p-4 border-b border-border">
        {!isCollapsed && (
          <h2 className="text-lg font-semibold text-foreground">
            Administração
          </h2>
        )}
        
        {onToggle && (
          <Button
            variant="ghost"
            size="icon"
            onClick={onToggle}
            className="h-8 w-8"
          >
            {isCollapsed ? (
              <ChevronRight className="h-4 w-4" />
            ) : (
              <ChevronLeft className="h-4 w-4" />
            )}
          </Button>
        )}
      </div>

      {/* Navigation */}
      <nav className="flex-1 p-2">
        <ul className="space-y-1">
          {sidebarItems.map((item) => {
            const Icon = item.icon;
            const isActive = isActiveRoute(item.href);

            return (
              <li key={item.href}>
                <Link
                  to={item.href}
                  className={cn(
                    "flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
                    "hover:bg-accent hover:text-accent-foreground",
                    isActive 
                      ? "bg-primary text-primary-foreground hover:bg-primary/90" 
                      : "text-muted-foreground"
                  )}
                  title={isCollapsed ? item.label : undefined}
                >
                  <Icon className="h-4 w-4 flex-shrink-0" />
                  
                  {!isCollapsed && (
                    <>
                      <span className="flex-1">{item.label}</span>
                      {item.badge && item.badge > 0 && (
                        <span className="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                          {item.badge > 99 ? '99+' : item.badge}
                        </span>
                      )}
                    </>
                  )}
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>

      {/* Settings */}
      <div className="p-2 border-t border-border">
        <Link
          to="#"
          className={cn(
            "flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
            "text-muted-foreground hover:bg-accent hover:text-accent-foreground"
          )}
          title={isCollapsed ? "Configurações" : undefined}
        >
          <Settings className="h-4 w-4 flex-shrink-0" />
          {!isCollapsed && <span>Configurações</span>}
        </Link>
      </div>
    </aside>
  );
};