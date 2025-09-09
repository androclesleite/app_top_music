#!/bin/bash

# Cores ANSI
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m' # No Color

echo -e "${CYAN}🚀 Iniciando aplicação Laravel...${NC}"

# Aguardar MySQL estar disponível
echo -e "${YELLOW}⏳ Aguardando MySQL...${NC}"
while ! php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
    echo -e "${RED}MySQL ainda não disponível, tentando novamente em 5 segundos...${NC}"
    sleep 5
done

echo -e "${GREEN}✅ MySQL conectado!${NC}"

# Executar migrations
echo -e "${BLUE}🔄 Executando migrations...${NC}"
php artisan migrate --force

# Executar seeders se não existirem dados
echo -e "${MAGENTA}🌱 Verificando seeders...${NC}"
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();")
if [ "$USER_COUNT" -eq 0 ]; then
    echo -e "${YELLOW}📝 Executando seeders...${NC}"
    php artisan db:seed --force
else
    echo -e "${GREEN}✅ Dados já existem, pulando seeders${NC}"
fi

# Limpar cache
echo -e "${CYAN}🧹 Limpando cache...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo -e "${GREEN}✅ Laravel pronto!${NC}"

# Iniciar supervisor (Nginx + PHP-FPM)
exec "$@"