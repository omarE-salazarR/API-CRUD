#!/bin/bash

# Función para pedir datos al usuario
prompt_user() {
    local key="$1"
    local prompt="$2"
    local file="$3"

    # Leer el dato del usuario
    read -p "$prompt: " INPUT

    # Si el archivo ya contiene la variable, reemplázala
    if grep -q "^$key=" "$file"; then
        sed -i "s/^$key=.*/$key=$INPUT/" "$file"
    else
        # Si no existe, añádela al final del archivo
        echo "$key=$INPUT" >> "$file"
    fi
}

# Crear archivos .env.local, .env.production, .env.testing y .env.example si no existen
create_env_files() {
    case $ENVIRONMENT in
        dev)
            if [ ! -f .env.local ]; then
                echo "Creando .env.local..."
                cp .env.example .env.local
            fi
            ;;
        prod)
            if [ ! -f .env.production ]; then
                echo "Creando .env.production..."
                cp .env.example .env.production
            fi
            ;;
        test)
            if [ ! -f .env.testing ]; then
                echo "Creando .env.testing..."
                cat <<EOL > .env.testing
APP_NAME=Laravel
APP_ENV=testing
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=:memory:
EOL
            fi
            ;;
        *)
            echo "Entorno no reconocido. Usa 'dev', 'prod', o 'test'."
            exit 1
            ;;
    esac
}

# Pedir al usuario las credenciales de base de datos para un entorno específico
configure_db() {
    local ENVIRONMENT=$1

    if [ "$ENVIRONMENT" == "dev" ]; then
        echo "Configurando base de datos para desarrollo..."
        prompt_user "DB_HOST" "Host de la base de datos" ".env.local"
        prompt_user "DB_PORT" "Puerto de la base de datos" ".env.local"
        prompt_user "DB_DATABASE" "Nombre de la base de datos" ".env.local"
        prompt_user "DB_USERNAME" "Usuario de la base de datos" ".env.local"
        prompt_user "DB_PASSWORD" "Contraseña de la base de datos" ".env.local"
    elif [ "$ENVIRONMENT" == "prod" ]; then
        echo "Configurando base de datos para producción..."
        prompt_user "DB_HOST" "Host de la base de datos" ".env.production"
        prompt_user "DB_PORT" "Puerto de la base de datos" ".env.production"
        prompt_user "DB_DATABASE" "Nombre de la base de datos" ".env.production"
        prompt_user "DB_USERNAME" "Usuario de la base de datos" ".env.production"
        prompt_user "DB_PASSWORD" "Contraseña de la base de datos" ".env.production"
    elif [ "$ENVIRONMENT" == "test" ]; then
        echo "Configurando base de datos para pruebas..."
        # No se solicitan credenciales para el entorno de pruebas
        return
    else
        echo "Entorno no reconocido. Usa 'dev', 'prod', o 'test'."
        exit 1
    fi
}

# Configurar el archivo .env para el entorno seleccionado
configure_env() {
    local ENVIRONMENT=$1

    # Asegúrate de que solo se configure el archivo para el entorno seleccionado
    case $ENVIRONMENT in
        dev)
            echo "Configurando el entorno de desarrollo..."
            cp .env.local .env
            sed -i 's/APP_ENV=production/APP_ENV=local/' .env
            sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
            sed -i 's/APP_URL=https:\/\/dominio.com/APP_URL=http:\/\/localhost/' .env
            ;;
        prod)
            echo "Configurando el entorno de producción..."
            cp .env.production .env
            sed -i 's/APP_ENV=local/APP_ENV=production/' .env
            sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
            sed -i 's/APP_URL=http:\/\/localhost/APP_URL=https:\/\/dominio.com/' .env
            ;;
        test)
            echo "Configurando el entorno de pruebas..."
            cp .env.testing .env
            ;;
        *)
            echo "Entorno no reconocido. Usa 'dev', 'prod', o 'test'."
            exit 1
            ;;
    esac
}

# Ejecutar pruebas unitarias
run_tests() {
    echo "Ejecutando pruebas unitarias..."
    php artisan test
}

# Verificar el argumento del entorno
ENVIRONMENT=$1

if [ -z "$ENVIRONMENT" ]; then
    echo "Por favor, especifica un entorno (dev, prod, o test)."
    exit 1
fi

# Crear los archivos .env necesarios según el entorno
create_env_files

# Configurar la base de datos para el entorno seleccionado
configure_db $ENVIRONMENT

# Configurar el entorno seleccionado
configure_env $ENVIRONMENT

# Limpiar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generar la clave de aplicación
echo "Generando la clave de la aplicación..."
php artisan key:generate

# Migrar la base de datos
echo "Ejecutando migraciones..."
php artisan migrate --seed

# Limpiar cachés
echo "Limpiando cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar pruebas si el entorno es de pruebas
if [ "$ENVIRONMENT" == "test" ]; then
    run_tests
fi

echo "Configuración completada para el entorno $ENVIRONMENT."
