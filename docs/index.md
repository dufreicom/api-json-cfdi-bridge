# Documentación

## Acerca de

Este proyecto es una JSON API para generar un CFDI a partir de datos JSON preformateados.

## Instalación

```shell
composer create-project dufrei/api-json-cfdi-bridge api-json-cfdi-bridge
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

## End points

