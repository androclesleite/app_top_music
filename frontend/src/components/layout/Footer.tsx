import React from 'react';
import { Music, Heart } from 'lucide-react';

export const Footer: React.FC = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Logo and Description */}
          <div className="flex flex-col items-center md:items-start space-y-4">
            <div className="flex items-center gap-2 text-lg font-bold text-primary">
              <Music className="h-6 w-6" />
              <span>Top 5 Tião Carreiro</span>
            </div>
            <p className="text-sm text-muted-foreground text-center md:text-left max-w-xs">
              Celebrando o legado eterno da música sertaneja raiz com as melhores 
              interpretações de Tião Carreiro.
            </p>
          </div>

          {/* About Section */}
          <div className="flex flex-col items-center md:items-start space-y-4">
            <h3 className="font-semibold text-foreground">Sobre o Projeto</h3>
            <div className="space-y-2 text-sm text-muted-foreground text-center md:text-left">
              <p>
                Uma homenagem ao maior violeiro do Brasil, Sebastião Antônio da Silva, 
                conhecido como Tião Carreiro.
              </p>
              <p>
                Aqui você encontra suas interpretações mais marcantes, 
                selecionadas com carinho pelos fãs.
              </p>
            </div>
          </div>

          {/* Links Section */}
          <div className="flex flex-col items-center md:items-start space-y-4">
            <h3 className="font-semibold text-foreground">Participe</h3>
            <div className="space-y-2 text-sm text-muted-foreground text-center md:text-left">
              <p>
                Tem uma música especial do Tião Carreiro que não está na lista?
              </p>
              <p>
                Envie sua sugestão através do formulário na página inicial!
              </p>
              <p>
                Toda contribuição é bem-vinda para manter viva essa tradição.
              </p>
            </div>
          </div>
        </div>

        {/* Bottom Section */}
        <div className="mt-8 pt-4 border-t border-border">
          <div className="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div className="flex items-center gap-1 text-sm text-muted-foreground">
              <span>Feito com</span>
              <Heart className="h-4 w-4 text-red-500 fill-current" />
              <span>para preservar a música sertaneja raiz</span>
            </div>
            
            <div className="text-sm text-muted-foreground">
              © {currentYear} Top 5 Tião Carreiro. Uma homenagem ao rei do violão.
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};