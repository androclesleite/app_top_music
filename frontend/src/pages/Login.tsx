import React, { useEffect } from 'react';
import { Navigate } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import LoginForm from '@/components/LoginForm';
import { useAuth } from '@/hooks/useAuth';
import { ROUTES } from '@/constants';

export const Login: React.FC = () => {
  const { isAuthenticated, isLoading } = useAuth();

  // Show loading while checking authentication
  if (isLoading) {
    return (
      <Layout>
        <div className="flex items-center justify-center min-h-[50vh]">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
        </div>
      </Layout>
    );
  }

  // Redirect to admin if already authenticated
  if (isAuthenticated) {
    return <Navigate to={ROUTES.ADMIN} replace />;
  }

  return (
    <Layout>
      <LoginForm />
    </Layout>
  );
};

export default Login;