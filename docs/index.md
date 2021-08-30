# Documentación

## Acerca de

Este proyecto es una JSON API para generar un CFDI a partir de datos JSON preformateados.

## Instalación por clonación del proyecto

```shell
git clone --branch main https://github.com/dufrei/api-json-cfdi-bridge.git api-json-cfdi-bridge
cd api-json-cfdi-bridge
composer install --no-dev --prefer-dist --optimize-autoloader
```

## Actualización de recursos XML

Se puede realizar la [actualización de los recursos SAT XML](recursos-sat-xml.md) con el siguiente script:

```shell
bin/resource-sat-xml-download
```

## Ejecución con variables de entorno

```shell
env AUTHORIZATION_TOKEN='$2y$10$guL9tPaNOeS/6rMGwIy.ZeH/1BmPbcRGiGzjjkRS7SDI0bM9mBMV' \
    FINKOK_PRODUCTION=yes FINKOK_USERNAME='usuario' FINKOK_PASSWORD='secreto' XMLRESOLVER_PATH="resources" \
    php -S 0.0.0.0:8080 -t public/
```

## Configuración

- [Variables de configuración del entorno](configuracion.md)

## Docker

Este proyecto provee los archivos necesarios para crear una imagen y la documentación para ejecutar contenedores.

- [Construcción de la imagen](docker-construir.md)
- [Crear una instancia de ejecución](docker-ejecutar.md)
