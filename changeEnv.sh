#!/bin/bash

# Función para configurar el entorno
configure_env() {
    local ENVIRONMENT=$1

    if [ "$ENVIRONMENT" == "dev" ]; then
        echo "Configurando el entorno de desarrollo..."
        cp .env.local .env
    elif [ "$ENVIRONMENT" == "prod" ]; then
        echo "Configurando el entorno de producción..."
        cp .env.production .env
    elif [ "$ENVIRONMENT" == "test" ]; then
        echo "Configurando el entorno de pruebas..."
        cp .env.testing .env
    else
        echo "Entorno no reconocido. Usa 'dev', 'prod', o 'test'."
        exit 1
    fi
}

# Verificar el argumento del entorno
ENVIRONMENT=$1

if [ -z "$ENVIRONMENT" ]; then
    echo "Por favor, especifica un entorno (dev, prod, o test)."
    exit 1
fi

# Configurar el entorno seleccionado
configure_env $ENVIRONMENT

# Otros pasos de configuración
