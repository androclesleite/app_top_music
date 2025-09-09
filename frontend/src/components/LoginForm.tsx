import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate, useLocation } from 'react-router-dom';
import { LogIn, Eye, EyeOff, Shield } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { useToast } from '@/components/ui/use-toast';
import { useAuth } from '@/hooks/useAuth';
import { LoginRequest } from '@/types';
import { ROUTES, MESSAGES } from '@/constants';

interface LoginFormData {
  email: string;
  password: string;
}

export const LoginForm: React.FC = () => {
  const { toast } = useToast();
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const from = (location.state as any)?.from?.pathname || ROUTES.ADMIN;

  const form = useForm<LoginFormData>({
    defaultValues: {
      email: '',
      password: '',
    },
  });

  const onSubmit = async (data: LoginFormData) => {
    setIsSubmitting(true);

    try {
      const loginData: LoginRequest = {
        email: data.email.trim(),
        password: data.password,
      };

      await login(loginData);

      toast({
        title: "Login realizado com sucesso!",
        description: MESSAGES.LOGIN_SUCCESS,
        variant: "success",
      });

      // Redirect to the intended page or admin dashboard
      navigate(from, { replace: true });

    } catch (error) {
      console.error('Login error:', error);
      
      toast({
        title: "Erro no login",
        description: error instanceof Error 
          ? error.message 
          : MESSAGES.LOGIN_ERROR,
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-[80vh] flex items-center justify-center">
      <Card className="w-full max-w-md">
        <CardHeader className="text-center">
          <div className="flex items-center justify-center gap-2 mb-4">
            <Shield className="h-8 w-8 text-primary" />
          </div>
          <CardTitle className="text-2xl">Acesso Administrativo</CardTitle>
          <CardDescription>
            Digite suas credenciais para acessar o painel de administração
          </CardDescription>
        </CardHeader>

        <CardContent>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
              {/* Email */}
              <FormField
                control={form.control}
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>E-mail</FormLabel>
                    <FormControl>
                      <Input
                        type="email"
                        placeholder="Digite seu e-mail"
                        autoComplete="email"
                        {...field}
                        disabled={isSubmitting}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
                rules={{
                  required: MESSAGES.REQUIRED_FIELD,
                  pattern: {
                    value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                    message: 'Digite um e-mail válido',
                  },
                }}
              />

              {/* Password */}
              <FormField
                control={form.control}
                name="password"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Senha</FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Input
                          type={showPassword ? 'text' : 'password'}
                          placeholder="Digite sua senha"
                          autoComplete="current-password"
                          {...field}
                          disabled={isSubmitting}
                        />
                        <Button
                          type="button"
                          variant="ghost"
                          size="sm"
                          className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                          onClick={() => setShowPassword(!showPassword)}
                          disabled={isSubmitting}
                        >
                          {showPassword ? (
                            <EyeOff className="h-4 w-4 text-muted-foreground" />
                          ) : (
                            <Eye className="h-4 w-4 text-muted-foreground" />
                          )}
                        </Button>
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
                rules={{
                  required: MESSAGES.REQUIRED_FIELD,
                  minLength: {
                    value: 6,
                    message: 'Senha deve ter pelo menos 6 caracteres',
                  },
                }}
              />

              {/* Submit Button */}
              <Button
                type="submit"
                className="w-full gap-2"
                disabled={isSubmitting}
              >
                {isSubmitting ? (
                  <>
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white" />
                    Entrando...
                  </>
                ) : (
                  <>
                    <LogIn className="h-4 w-4" />
                    Entrar
                  </>
                )}
              </Button>
            </form>
          </Form>

          {/* Help Text */}
          <div className="mt-6 text-center">
            <p className="text-sm text-muted-foreground">
              Área restrita aos administradores do site.
            </p>
            <p className="text-xs text-muted-foreground mt-1">
              Entre em contato se precisar de acesso.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default LoginForm;