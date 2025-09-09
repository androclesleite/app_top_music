import React, { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { Save, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Form, FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { useToast } from '@/components/ui/use-toast';
import { Song, CreateSongRequest, UpdateSongRequest } from '@/types';
import { isValidYouTubeUrl, normalizeYouTubeUrl } from '@/lib/utils';
import { MESSAGES } from '@/constants';

interface AdminSongFormProps {
  song?: Song | null;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSave: (data: CreateSongRequest | UpdateSongRequest) => Promise<void>;
}

interface SongFormData {
  title: string;
  artist: string;
  youtube_url: string;
  position?: number;
}

export const AdminSongForm: React.FC<AdminSongFormProps> = ({
  song,
  open,
  onOpenChange,
  onSave,
}) => {
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const isEditing = !!song;

  const form = useForm<SongFormData>({
    defaultValues: {
      title: '',
      artist: '',
      youtube_url: '',
      position: undefined,
    },
  });

  // Reset form when song changes or dialog opens/closes
  useEffect(() => {
    if (open) {
      if (song) {
        form.reset({
          title: song.title,
          artist: song.artist,
          youtube_url: song.youtube_url,
          position: song.position || undefined,
        });
      } else {
        form.reset({
          title: '',
          artist: '',
          youtube_url: '',
          position: undefined,
        });
      }
    }
  }, [song, open, form]);

  const onSubmit = async (data: SongFormData) => {
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
      const songData = {
        title: data.title.trim(),
        artist: data.artist.trim(),
        youtube_url: normalizedUrl,
        position: data.position || undefined,
      };

      await onSave(songData);

      toast({
        title: isEditing ? "Música atualizada!" : "Música criada!",
        description: isEditing ? MESSAGES.SONG_UPDATED : MESSAGES.SONG_CREATED,
        variant: "success",
      });

      onOpenChange(false);

    } catch (error) {
      console.error('Error saving song:', error);
      
      toast({
        title: `Erro ao ${isEditing ? 'atualizar' : 'criar'} música`,
        description: error instanceof Error ? error.message : "Tente novamente mais tarde.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleCancel = () => {
    form.reset();
    onOpenChange(false);
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[525px]">
        <DialogHeader>
          <DialogTitle>
            {isEditing ? 'Editar Música' : 'Nova Música'}
          </DialogTitle>
          <DialogDescription>
            {isEditing 
              ? 'Edite as informações da música abaixo.'
              : 'Adicione uma nova música à lista de Tião Carreiro.'
            }
          </DialogDescription>
        </DialogHeader>

        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
            {/* Title */}
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

            {/* Position */}
            <FormField
              control={form.control}
              name="position"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Posição no Ranking</FormLabel>
                  <FormControl>
                    <Input
                      type="number"
                      min="1"
                      max="100"
                      placeholder="Deixe vazio para auto-definir"
                      {...field}
                      value={field.value || ''}
                      onChange={(e) => {
                        const value = e.target.value;
                        field.onChange(value ? parseInt(value) : undefined);
                      }}
                      disabled={isSubmitting}
                    />
                  </FormControl>
                  <FormDescription>
                    Posições 1-5 são reservadas para o Top 5. Deixe vazio para definir automaticamente.
                  </FormDescription>
                  <FormMessage />
                </FormItem>
              )}
              rules={{
                min: {
                  value: 1,
                  message: 'Posição deve ser maior que 0',
                },
                max: {
                  value: 100,
                  message: 'Posição deve ser menor que 100',
                },
              }}
            />

            <DialogFooter className="gap-2 sm:gap-0">
              <Button
                type="button"
                variant="outline"
                onClick={handleCancel}
                disabled={isSubmitting}
              >
                <X className="h-4 w-4 mr-2" />
                Cancelar
              </Button>
              <Button
                type="submit"
                disabled={isSubmitting}
              >
                {isSubmitting ? (
                  <>
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2" />
                    Salvando...
                  </>
                ) : (
                  <>
                    <Save className="h-4 w-4 mr-2" />
                    {isEditing ? 'Atualizar' : 'Criar'}
                  </>
                )}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

export default AdminSongForm;