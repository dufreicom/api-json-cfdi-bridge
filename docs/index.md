# Documentación

## Acerca de

Este proyecto es una JSON API para generar un CFDI a partir de datos JSON preformateados.

## Instalación

```shell
composer create-project dufrei/api-json-cfdi-bridge api-json-cfdi-bridge

cat .env.example .env | \
    sed 's/AUTHORIZATION_KEY=.*/$(php bin/create-authorization-token.php | grep -o "AUTHORIZATION_KEY=.*")' 
    sed 's/FINKOK_ENVIRONMENT=.*/FINKOK_ENVIRONMENT=production/' | \
    sed 's/FINKOK_USERNAME=.*/FINKOK_USERNAME=your-finkok-username/' | \
    sed 's/FINKOK_PASSWORD=.*/FINKOK_PASSWORD=your-finkok-password/'

php -S 0.0.0.0:8080 -t public/
```

## Configuración

- [Variables de configuración del entorno](configuracion/entorno.md)

## Seguridad

En las llamadas a la API es necesario entregar un HTTP Token autorización.

- [Acerca del token de acceso](seguridad-token-acceso.md)

## End points

