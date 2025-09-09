import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from '@/context/AuthContext';
import { Toaster } from '@/components/ui/toaster';
import ProtectedRoute from '@/components/ProtectedRoute';
import ErrorBoundary from '@/components/ErrorBoundary';
import Home from '@/pages/Home';
import Login from '@/pages/Login';
import Admin from '@/pages/Admin';
import AdminSongs from '@/pages/AdminSongs';
import AdminSuggestionsPage from '@/pages/AdminSuggestions';
import NotFound from '@/pages/NotFound';
import { ROUTES } from '@/constants';

function App() {
  return (
    <ErrorBoundary>
      <AuthProvider>
        <Router>
          <div className="App">
            <Routes>
              {/* Public Routes */}
              <Route path={ROUTES.HOME} element={<Home />} />
              <Route path={ROUTES.LOGIN} element={<Login />} />

              {/* Protected Routes */}
              <Route 
                path={ROUTES.ADMIN} 
                element={
                  <ProtectedRoute>
                    <Admin />
                  </ProtectedRoute>
                } 
              />
              <Route 
                path={ROUTES.ADMIN_SONGS} 
                element={
                  <ProtectedRoute>
                    <AdminSongs />
                  </ProtectedRoute>
                } 
              />
              <Route 
                path={ROUTES.ADMIN_SUGGESTIONS} 
                element={
                  <ProtectedRoute>
                    <AdminSuggestionsPage />
                  </ProtectedRoute>
                } 
              />

              {/* Catch all - 404 */}
              <Route path="*" element={<NotFound />} />
            </Routes>

            {/* Global Toast Container */}
            <Toaster />
          </div>
        </Router>
      </AuthProvider>
    </ErrorBoundary>
  );
}

export default App;