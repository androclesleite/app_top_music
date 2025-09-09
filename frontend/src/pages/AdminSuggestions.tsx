import React, { useState } from 'react';
import { AdminLayout } from '@/components/layout/Layout';
import { Sidebar } from '@/components/layout/Sidebar';
import AdminSuggestions from '@/components/AdminSuggestions';

export const AdminSuggestionsPage: React.FC = () => {
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

  return (
    <AdminLayout
      sidebar={
        <Sidebar 
          isCollapsed={sidebarCollapsed}
          onToggle={() => setSidebarCollapsed(!sidebarCollapsed)}
        />
      }
    >
      <AdminSuggestions />
    </AdminLayout>
  );
};

export default AdminSuggestionsPage;