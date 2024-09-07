# Shorter-back


## Tabla de Contenidos

- [Introducción](#introducción)
- [Instalación](#instalación)
  - [Clonar el Repositorio](#clonar-el-repositorio)
  - [Instalar Composer](#instalar-composer)
  - [Instalar Ambientes](#instalar-ambientes)
  - [Correr el Proyecto](#correr-el-proyecto)
- [Intercambiar Entre Ambientes](#intercambiar-entre-ambientes)
- [Documentación de API (Postman)](#postman)

## Introducción

Este repositorio contiene la aplicación **Acortador de URL Backend**. Utiliza el script `init.sh` para configurar el entorno de desarrollo, producción y pruebas de manera automatizada.

## Instalación

### Clonar el Repositorio

Clona el repositorio en tu máquina local:

```bash
git clone https://github.com/omarE-salazarR/shorter-spot2.git
cd shorter-spot2
```
#### Clonar el Repositorio

Clona el repositorio en tu máquina local:

#### Instalar Composer
Instala las dependencias del proyecto utilizando Composer:
```bash
- composer install
```
##### Instalar Ambientes
Ejecuta el script init.sh para configurar el entorno:
Este script te solicitará el ambiente a instalar y los datos de la base de datos para realizar la configuración del ambiente.
```bash
- ./init.sh
```

###### Correr el Proyecto
Inicia el servidor de desarrollo de Laravel:
```bash
php artisan serve
```
###### Intercambiar Entre Ambientes
Para cambiar entre diferentes configuraciones de ambiente, utiliza los siguientes comandos:
```bash
cp .env.production .env
cp .env.local .env
cp .env.test .env
```

###### postman
La api desplegada esta relacionada en la siguiente documentación de postman:
API DOC: [Documentación de API (Postman)](https://documenter.getpostman.com/view/17415558/2sAXjRW9NA)
API URL: https://short.alertsiep.top
