import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Send, Music, CheckCircle, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { useToast } from '@/components/ui/use-toast';
import { useSuggestions } from '@/hooks/useSuggestions';
import { CreateSuggestionRequest } from '@/types';
import { isValidYouTubeUrl, normalizeYouTubeUrl } from '@/lib/utils';
import { MESSAGES } from '@/constants';

interface SuggestionFormData {
  title: string;
  artist: string;
  youtube_url: string;
  suggested_by_name?: string;
  suggested_by_email?: string;
}

export const SuggestionForm: React.FC = () => {
  const { toast } = useToast();
  const { actions } = useSuggestions();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false);

  const form = useForm<SuggestionFormData>({
    defaultValues: {
      title: '',
      artist: '',
      youtube_url: '',
      suggested_by_name: '',
      suggested_by_email: '',
    },
  });

  const onSubmit = async (data: SuggestionFormData) => {
    // Normalize and validate YouTube URL
    const normalizedUrl = normalizeYouTubeUrl(data.youtube_url.trim());
    if (!normalizedUrl) {
      form.setError('youtube_url', {
        type: 'manual',
        message: MESSAGES.INVALID_YOUTUBE_URL,
      });
      return;
    }

    setIsSubmitting(true);

    try {
      const suggestionData: CreateSuggestionRequest = {
        title: data.title.trim(),
        artist: data.artist.trim(),
        youtube_url: normalizedUrl,
        suggested_by_name: data.suggested_by_name?.trim() || undefined,
        suggested_by_email: data.suggested_by_email?.trim() || undefined,
      };

      await actions.createSuggestion(suggestionData);

      setIsSubmitted(true);
      form.reset();

      toast({
        title: "Sugestão enviada!",
        description: MESSAGES.SUGGESTION_CREATED,
        variant: "success",
      });

      // Reset submitted state after 5 seconds
      setTimeout(() => {
        setIsSubmitted(false);
      }, 5000);

    } catch (error) {
      console.error('Error submitting suggestion:', error);
      
      toast({
        title: "Erro ao enviar sugestão",
        description: error instanceof Error ? error.message : "Tente novamente mais tarde.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (isSubmitted) {
    return (
      <section id="suggest-song" className="py-12 bg-muted/30">
        <div className="container mx-auto px-4">
          <Card className="max-w-2xl mx-auto">
            <CardContent className="p-8 text-center">
              <CheckCircle className="h-16 w-16 text-green-500 mx-auto mb-4" />
              <h3 className="text-2xl font-bold text-foreground mb-2">
                Sugestão Enviada com Sucesso!
              </h3>
              <p className="text-muted-foreground mb-4">
                Obrigado por contribuir com o projeto! Sua sugestão será analisada 
                e poderá ser adicionada à lista das músicas de Tião Carreiro.
              </p>
              <Button 
                onClick={() => setIsSubmitted(false)}
                variant="outline"
              >
                Enviar outra sugestão
              </Button>
            </CardContent>
          </Card>
        </div>
      </section>
    );
  }

  return (
    <section id="suggest-song" className="py-12 bg-muted/30">
      <div className="container mx-auto px-4">
        <Card className="max-w-2xl mx-auto">
          <CardHeader className="text-center">
            <div className="flex items-center justify-center gap-2 mb-4">
              <Music className="h-6 w-6 text-primary" />
              <CardTitle className="text-2xl">Sugira uma Música</CardTitle>
            </div>
            <CardDescription className="text-base">
              Conhece alguma interpretação marcante do Tião Carreiro que não está na nossa lista? 
              Compartilhe conosco e ajude a preservar o legado da música sertaneja raiz!
            </CardDescription>
          </CardHeader>

          <CardContent>
            <Form {...form}>
              <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                {/* Song Title */}
                <FormField
                  control={form.control}
                  name="title"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Título da Música *</FormLabel>
                      <FormControl>
                        <Input
                          placeholder="Ex: Pagode em Brasília"
                          {...field}
                          disabled={isSubmitting}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                  rules={{
                    required: MESSAGES.REQUIRED_FIELD,
                    minLength: {
                      value: 2,
                      message: 'Título deve ter pelo menos 2 caracteres',
                    },
                  }}
                />

                {/* Artist */}
                <FormField
                  control={form.control}
                  name="artist"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Artista/Intérprete *</FormLabel>
                      <FormControl>
                        <Input
                          placeholder="Ex: Tião Carreiro e Pardinho"
                          {...field}
                          disabled={isSubmitting}
                        />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                  rules={{
                    required: MESSAGES.REQUIRED_FIELD,
                    minLength: {
                      value: 2,
                      message: 'Nome do artista deve ter pelo menos 2 caracteres',
                    },
                  }}
                />

                {/* YouTube URL */}
                <FormField
                  control={form.control}
                  name="youtube_url"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Link do YouTube *</FormLabel>
                      <FormControl>
                        <Input
                          placeholder="https://www.youtube.com/watch?v=..."
                          {...field}
                          disabled={isSubmitting}
                        />
                      </FormControl>
                      <FormDescription>
                        Cole aqui o link completo do YouTube da música
                      </FormDescription>
                      <FormMessage />
                    </FormItem>
                  )}
                  rules={{
                    required: MESSAGES.REQUIRED_FIELD,
                    validate: (value) => 
                      isValidYouTubeUrl(value) || MESSAGES.INVALID_YOUTUBE_URL,
                  }}
                />

                {/* Divider */}
                <div className="border-t pt-6">
                  <h4 className="text-sm font-medium text-muted-foreground mb-4">
                    Informações opcionais (para contato)
                  </h4>
                </div>

                {/* Suggester Name */}
                <FormField
                  control={form.control}
                  name="suggested_by_name"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Seu Nome</FormLabel>
                      <FormControl>
                        <Input
                          placeholder="Como você gostaria de ser identificado?"
                          {...field}
                          disabled={isSubmitting}
                        />
                      </FormControl>
                    </FormItem>
                  )}
                />

                {/* Suggester Email */}
                <FormField
                  control={form.control}
                  name="suggested_by_email"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Seu E-mail</FormLabel>
                      <FormControl>
                        <Input
                          type="email"
                          placeholder="seu@email.com"
                          {...field}
                          disabled={isSubmitting}
                        />
                      </FormControl>
                      <FormDescription>
                        Para entrarmos em contato se necessário
                      </FormDescription>
                    </FormItem>
                  )}
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
                      Enviando...
                    </>
                  ) : (
                    <>
                      <Send className="h-4 w-4" />
                      Enviar Sugestão
                    </>
                  )}
                </Button>
              </form>
            </Form>

            {/* Help Text */}
            <div className="mt-6 p-4 bg-muted rounded-lg">
              <div className="flex gap-3">
                <AlertCircle className="h-5 w-5 text-muted-foreground flex-shrink-0 mt-0.5" />
                <div className="text-sm text-muted-foreground">
                  <p className="font-medium mb-1">Dica importante:</p>
                  <p>
                    Procure por interpretações originais ou marcantes do Tião Carreiro. 
                    Sua sugestão será analisada pelos administradores antes de ser 
                    adicionada à lista oficial.
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </section>
  );
};

export default SuggestionForm;