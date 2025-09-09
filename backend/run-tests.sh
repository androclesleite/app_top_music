#!/bin/bash

echo "🧪 Executando testes unitários do Laravel..."
echo "============================================"

# Executar todos os testes
php artisan test --stop-on-failure

echo ""
echo "✅ Testes concluídos!"