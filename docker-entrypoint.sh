#!/bin/bash
set -e

echo "🚀 Iniciando aplicação Laravel..."

# Verificar e instalar dependências se necessário
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Vendor não encontrado, instalando dependências PHP..."
    composer install --no-interaction --prefer-dist --no-scripts
fi

if [ ! -d "node_modules" ]; then
    echo "Node modules não encontrado, instalando dependências JS..."
    npm install
fi

# Aguardar banco de dados estar disponível
echo "Aguardando banco de dados..."
until pg_isready -h db -p 5432 -U postgres >/dev/null 2>&1; do
    echo "Banco não disponível, aguardando 3 segundos..."
    sleep 3
done

echo "Banco de dados disponível!"

# Aguardar mais um pouco para garantir
sleep 2

# Executar migrações UMA vez
echo "Executando migrações..."
php artisan migrate --force

# Verificar se há dados nas tabelas
USERS_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null || echo "0")

if [ "$USERS_COUNT" = "0" ]; then
    echo "Executando seeders (primeira execução)..."
    php artisan db:seed --class=AdminCoreSeeder --force
    php artisan db:seed --class=DormitorioSeeder --force
else
    echo "Banco já possui dados, pulando seeders..."
fi

# Limpar e otimizar caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Comportamento baseado no APP_ENV
if [ "$APP_ENV" = "local" ]; then
    echo "Modo DESENVOLVIMENTO detectado"
    echo "Hot reload será ativado!"
    
    # Desabilitar OPcache para desenvolvimento
    echo "Desabilitando OPcache para hot reload..."
    cat > /usr/local/etc/php/conf.d/opcache.ini << 'EOF'
opcache.enable=0
EOF
    
    # NÃO cachear em desenvolvimento
    echo "Caches desabilitados para desenvolvimento"
    
    # Verificar se node_modules existe, senão instalar
    if [ ! -d "node_modules" ]; then
        echo "Instalando dependências Node.js..."
        npm install
    fi
    
else
    echo "⚡ Modo PRODUÇÃO detectado"
    
    # Habilitar e otimizar OPcache para produção
    echo "Habilitando OPcache otimizado..."
    cat > /usr/local/etc/php/conf.d/opcache.ini << 'EOF'
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
EOF
    
    # Otimizar caches Laravel para produção
    echo "Otimizando caches Laravel..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Build dos assets
    echo "Compilando assets para produção..."
    npm run build
fi

# Garantir que o storage link existe
echo "🔗 Verificando storage link..."
if [ ! -L public/storage ]; then
    echo "Criando storage link..."
    php artisan storage:link --no-interaction 2>/dev/null || {
        echo "Aviso: Não foi possível criar storage link automaticamente"
        echo "Execute manualmente: docker-compose exec app php artisan storage:link"
    }
else
    echo "Storage link já existe, pulando..."
fi

# Ajustar permissões finais
echo "Ajustando permissões..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

# Garantir que public/storage tenha as permissões corretas se existir
# Ignorar erros de permissão em symlinks (comum em volumes Docker)
if [ -L public/storage ]; then
    chown -h www-data:www-data public/storage 2>/dev/null || true
fi

echo "Aplicação pronta! Iniciando Apache..."

# Se estiver em modo desenvolvimento, iniciar Vite dev server em background
if [ "$APP_ENV" = "local" ]; then
    echo "Iniciando Vite dev server para hot reload..."
    
    # Criar script de inicialização do Vite
    cat > /usr/local/bin/start-vite.sh << 'VITE_SCRIPT'
#!/bin/bash
cd /var/www/html
exec npm run dev -- --host 0.0.0.0
VITE_SCRIPT
    
    chmod +x /usr/local/bin/start-vite.sh
    
    # Iniciar Vite em background e redirecionar logs
    /usr/local/bin/start-vite.sh > /var/log/vite.log 2>&1 &
    VITE_PID=$!
    
    echo "✅ Vite dev server iniciado (PID: $VITE_PID)"
    echo "📋 Logs: docker-compose exec app tail -f /var/log/vite.log"
    
    # Aguardar alguns segundos para garantir que o Vite iniciou
    sleep 3
    
    # Verificar se o processo ainda está rodando
    if ps -p $VITE_PID > /dev/null 2>&1; then
        echo "Vite confirmado rodando na porta 5173"
    else
        echo "Aviso: Vite pode ter falhado ao iniciar"
        echo "Verifique: docker-compose exec app cat /var/log/vite.log"
    fi
fi

# Iniciar Apache
exec apache2-foreground