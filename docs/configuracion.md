# Configuración de entorno

Estas variables de configuración son utilizadas por el proyecto y controlan su ejecución.

## `AUTHORIZATION_TOKEN`

Establece el *hash* del token de autorización. Como todos los tokens, si se sospecha que se ha perdido,
se debe usar uno nuevo, para ello solo es necesario cambiar este valor.

### Uso de `bin/create-authorization-token.php`

Esta es una herramienta que genera un token de autorización aleatorio y su *hash* para almacenarlo como
una variable de configuración, y nos muestra el `AUTHORIZATION_TOKEN` y la cabecera HTTP `Authorization`:

```shell
php bin/create-authorization-token.php
```

```text
Set up the environment with AUTHORIZATION_TOKEN='$2y$10$WW0N4Ei1zUId7q5uapV2WOlbx9EQJyhLcc3kGkrhkey9I6ip1cCgS'
Your client must use the HTTP authorization header:
   Authorization: Bearer ad418b254561de0b16253a312b360f3973ca8a16
```

## Configuración de Finkok

Las variables `FINKOK_PRODUCTION`, `FINKOK_USERNAME` y `FINKOK_PASSWORD` especifican los parámetros para poderse
conectar a Finkok.

Si `FINKOK_PRODUCTION` es un valor positivo como `1`, `true`, `yes` o `on` entonces usará el entorno productivo.
Si es cualquier otro valor entonces usará el entorno de desarrollo.

## `XMLRESOLVER_PATH`

Para que la aplicación no tenga que estar descargando los archivos XSLT cuando crea la *Cadena de origen*
es importante tener una copia almacenada local de los mismos.

Si `XMLRESOLVER_PATH` es un valor vacío, entonces siempre usará los archivos desde el sitio del SAT.

Si `XMLRESOLVER_PATH` contiene un valor se considerará como una ruta relativa o absoluta.
Es absoluta si inicia con `/`, es relativa en caso contrario.

## `SAXONB_PATH`

Vea: <https://cfdiutils.readthedocs.io/es/latest/componentes/cadena-de-origen.html#php-y-xlst-version-2>

Si se especifica y lo tiene instalado, se puede usar el *Saxon-B XSLT Processor*,
por ejemplo: `SAXONB_PATH=/usr/bin/saxonb-xslt`.
