import React, { useState } from 'react';
import { AdminLayout } from '@/components/layout/Layout';
import { Sidebar } from '@/components/layout/Sidebar';
import SongManagement from '@/components/SongManagement';

export const AdminSongs: React.FC = () => {
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
      <SongManagement />
    </AdminLayout>
  );
};

export default AdminSongs;