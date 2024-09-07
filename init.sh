#!/bin/bash

# Advertencia inicial
echo "Advertencia: Asegúrate de tener dos bases de datos configuradas (desarrollo y producción) y tener a la mano los accesos necesarios."
read -p "Presiona [Enter] para continuar o [Ctrl+C] para cancelar..."

# Selección del entorno
echo "Selecciona el entorno para configurar:"
echo "1. Desarrollo (dev)"
echo "2. Producción (prod)"
echo "3. Pruebas (test)"
read -p "Introduce el número del entorno (1, 2, 3): " SELECTION

case $SELECTION in
    1)
        ENVIRONMENT="dev"
        ;;
    2)
        ENVIRONMENT="prod"
        ;;
    3)
        ENVIRONMENT="test"
        ;;
    *)
        echo "Selección no válida. Usa 1, 2 o 3."
        exit 1
        ;;
esac

# Configuración para el entorno seleccionado
echo "Configurando entorno $ENVIRONMENT..."
./setup.sh $ENVIRONMENT

echo "Configuración completada para el entorno $ENVIRONMENT."
