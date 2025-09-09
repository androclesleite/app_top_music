import React from 'react';
import { Link } from 'react-router-dom';
import { Home, ArrowLeft, Music } from 'lucide-react';
import { Layout } from '@/components/layout/Layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ROUTES } from '@/constants';

export const NotFound: React.FC = () => {
  return (
    <Layout>
      <div className="min-h-[60vh] flex items-center justify-center">
        <Card className="max-w-lg mx-auto text-center">
          <CardContent className="p-12">
            {/* Large 404 */}
            <div className="mb-8">
              <h1 className="text-8xl font-bold text-primary opacity-20 mb-4">404</h1>
              <div className="flex justify-center mb-4">
                <Music className="h-16 w-16 text-muted-foreground" />
              </div>
            </div>

            {/* Error message */}
            <div className="mb-8">
              <h2 className="text-2xl font-bold text-foreground mb-4">
                Página Não Encontrada
              </h2>
              <p className="text-muted-foreground mb-4">
                Parece que esta música saiu de tom... A página que você está 
                procurando não existe ou foi movida para outro lugar.
              </p>
              <p className="text-sm text-muted-foreground">
                Que tal voltar ao repertório principal?
              </p>
            </div>

            {/* Action buttons */}
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button asChild size="lg" className="gap-2">
                <Link to={ROUTES.HOME}>
                  <Home className="h-4 w-4" />
                  Voltar ao Início
                </Link>
              </Button>
              
              <Button asChild variant="outline" size="lg" className="gap-2">
                <button onClick={() => window.history.back()}>
                  <ArrowLeft className="h-4 w-4" />
                  Página Anterior
                </button>
              </Button>
            </div>

            {/* Fun message */}
            <div className="mt-8 p-4 bg-muted/50 rounded-lg">
              <p className="text-sm text-muted-foreground italic">
                "A vida é como uma música: tem que ser tocada do começo ao fim, 
                mas às vezes perdemos o compasso no meio do caminho."
              </p>
              <p className="text-xs text-muted-foreground mt-2">
                - Inspirado na filosofia sertaneja
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
};

export default NotFound;