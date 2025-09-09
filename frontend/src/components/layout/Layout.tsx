import React from 'react';
import { Header } from './Header';
import { Footer } from './Footer';

interface LayoutProps {
  children: React.ReactNode;
}

export const Layout: React.FC<LayoutProps> = ({ children }) => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20 flex flex-col">
      <Header />
      
      <main className="flex-1 container mx-auto px-4 py-8">
        {children}
      </main>
      
      <Footer />
    </div>
  );
};

interface AdminLayoutProps {
  children: React.ReactNode;
  sidebar?: React.ReactNode;
}

export const AdminLayout: React.FC<AdminLayoutProps> = ({ 
  children, 
  sidebar 
}) => {
  return (
    <div className="min-h-screen bg-background flex flex-col">
      <Header />
      
      <div className="flex-1 flex">
        {sidebar && (
          <div className="hidden lg:block">
            {sidebar}
          </div>
        )}
        
        <main className="flex-1 overflow-auto">
          <div className="p-6">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};