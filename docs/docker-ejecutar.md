# Crear una instancia de ejecución

Una vez que has [construido la imagen de docker](docker-construir.md) entonces puedes ejecutar la instancia.

Para estos ejemplos se supone que la imagen se construyó con el nombre `api-json-cfdi-bridge`,
pero podría ser cualquiera, o incluso con un *tag* específico, como `api-json-cfdi-bridge:testing`.

## Ejecución única

La siguiente instrucción ejecutará un contenedor de nombre aleatorio, destruirá la imagen en cuanto se termine su ejecución,
publicando el puerto `80` del contenedor en el puerto `8082` local. Podrás acceder a la API en `http://localhost:8082/`.

```shell
docker run --rm --publish 8082:80 api-json-cfdi-bridge
```

## Ejecución en segundo plano

En este caso, se crea una instancia con el nombre `api-json-cfdi-bridge` y se ejecuta en segundo plano,
de esta forma, se puede detener y volver a ejecutar usando `docker stop` y `docker start`.

```shell
# ejecución de la instancia
docker run --detach=true --name api-json-cfdi-bridge --publish 8082:80 api-json-cfdi-bridge

# detener la instancia
docker stop api-json-cfdi-bridge

# volver a levantar la instancia
docker start api-json-cfdi-bridge

# ver todas las instancias que existen
docker ps -a
```

## Parámetros de entorno

La aplicación requiere [configuración de entorno](configuracion.md), para pasar los valores es necesario que
especifiques estos valores desde que ejecutas con `docker run --env` o con `docker run --env-file`, por ejemplo:

Nota: Observa el uso de las comillas simples y no de comillas dobles.

```shell
docker run --rm --publish 8082:80 \
    --env AUTHORIZATION_TOKEN='$2y$10$guL9tPaNOeS/6rMGwIy.ZeH/1BmPbcRGiGzjjkRS7SDI0bM9mBMV' \
    --env FINKOK_PRODUCTION=yes \
    --env FINKOK_USERNAME='usuario' \
    --env FINKOK_PASSWORD='secreto' \
    --env XMLRESOLVER_PATH="resources" \
    api-json-cfdi-bridge
```

O bien, si tienes un archivo de entorno, suponiendo en `/etc/api-json-cfdi-bridge/environment` podrías usar:

```shell
docker run --rm --publish 8082:80 --env-file /etc/api-json-cfdi-bridge/environment api-json-cfdi-bridge
```

## Obtener las claves

Si se está ejecutando docker entonces lo más conveniente es generar claves aleatorias, para ello bastará
con ejecutar el siguiente comando:

```shell
docker run --rm -it --entrypoint bin/create-authorization-token.php api-json-cfdi-bridge
```

Donde mostrará la siguiente información y sirve para usar valores válidos para autenticación:

```text
Set up the environment with AUTHORIZATION_TOKEN='$2y$10$WW0N4Ei1zUId7q5uapV2WOlbx9EQJyhLcc3kGkrhkey9I6ip1cCgS'
Your client must use the HTTP authorization header:
   Authorization: Bearer ad418b254561de0b16253a312b360f3973ca8a16
```

## Actualización de recursos

Con el siguiente comando se usará una imagen que esté en ejecución llamada `api-json-cfdi-bridge`
y se ejecutará `bin/resource-sat-xml-download`. Como el comando usa la configuración de la variable
de entorno `XMLRESOLVER_PATH` y esta debió de configurarse desde la ejecución del proyecto, entonces
no es necesario pasar ningún parámetro adicional.

```shell
docker exec api-json-cfdi-bridge bin/resource-sat-xml-download
```

## Saxon-B XSLT Processor

La imagen de docker ya incluye el procesador XSLT Saxon-B, lo puede usar si especifica la variable de entorno
`SAXONB_PATH=/usr/bin/saxonb-xslt`.
