#!/bin/bash

echo "Iniciando aplicação Laravel..."

if [ -f "public/hot" ]; then
    echo "Removendo arquivo public/hot de execução anterior..."
    rm -f public/hot
fi

# =====================================================
# DEPENDÊNCIAS
# =====================================================

if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Vendor não encontrado, instalando dependências PHP..."
    composer install --no-interaction --prefer-dist --no-scripts || exit 1
fi

if [ ! -d "node_modules" ]; then
    echo "Node modules não encontrado, instalando dependências JS..."
    npm install || exit 1
fi

# =====================================================
# BANCO DE DADOS
# =====================================================

echo "Aguardando banco de dados..."
until pg_isready -h db -p 5432 -U postgres >/dev/null 2>&1; do
    echo "Banco não disponível, aguardando 3 segundos..."
    sleep 3
done
echo "Banco disponível!"
sleep 2

php artisan migrate --force || exit 1

USERS_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tr -d '[:space:]' || echo "0")
if [ "$USERS_COUNT" = "0" ]; then
    echo "Executando seeders (primeira execução)..."
    php artisan db:seed --class=AdminCoreSeeder --force
    php artisan db:seed --class=DormitorioSeeder --force
else
    echo "Banco já possui dados ($USERS_COUNT usuários), pulando seeders..."
fi

# =====================================================
# CACHES
# =====================================================

echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# =====================================================
# COMPORTAMENTO POR AMBIENTE
# =====================================================

if [ "$APP_ENV" = "local" ]; then
    echo "Modo DESENVOLVIMENTO"

    cat > /usr/local/etc/php/conf.d/opcache.ini << 'EOF'
opcache.enable=0
EOF

else
    echo "Modo PRODUÇÃO"

    cat > /usr/local/etc/php/conf.d/opcache.ini << 'EOF'
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
EOF

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Build apenas se public/build não existir ainda
    if [ ! -f "public/build/manifest.json" ]; then
        echo "Compilando assets para produção..."
        npm run build || exit 1
        echo "Assets compilados!"
    else
        echo "Assets já compilados, pulando build"
    fi
fi

# =====================================================
# STORAGE LINK
# =====================================================

if [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction 2>/dev/null || true
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

# =====================================================
# VITE DEV SERVER (apenas local)
# =====================================================

if [ "$APP_ENV" = "local" ]; then
    echo "Iniciando Vite dev server..."

    nohup npm run dev -- --host 0.0.0.0 --port 5173 > /var/log/vite.log 2>&1 &
    VITE_PID=$!

    # Aguarda o Vite criar public/hot
    echo "Aguardando Vite ficar pronto..."
    WAIT=0
    while [ ! -f "public/hot" ] && [ $WAIT -lt 30 ]; do
        sleep 1
        WAIT=$((WAIT + 1))
        if ! kill -0 $VITE_PID 2>/dev/null; then
            echo "Vite falhou! Logs:"
            cat /var/log/vite.log
            echo "Continuando sem hot reload..."
            break
        fi
    done

    if [ -f "public/hot" ]; then
        echo "Vite pronto na porta 5173!"
    else
        echo "Vite ainda não criou public/hot. Verifique: docker-compose exec app tail -f /var/log/vite.log"
    fi
fi

echo "Iniciando Apache..."
exec apache2-foreground