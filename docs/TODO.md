# dufrei/api-json-cfdi-bridge To Do List

## Script de actualización de recursos

El proyecto debería tener un script para descargar los archivos XSLT actualizados que sirven para
generar la cadena de origen.

¿Deberían estos archivos estar almacenados en el proyecto como parte del código,
para que al hacer la ejecución ya estén disponibles?

¿Se consideran parte de las dependencias del proyecto, luego entonces, se instalan al momento de "construir"
la aplicación y se actualizan periódicamente?

## Probar los errores

Se necesita simular un error de comunicación con Finkok, este error no debe tratarse como un error 400.

## Registro

¿Se deben generar registros de error? Si es así, ¿qué se hace con ellos?.

## Documentación

Completar la documentación que está en `docs/`.

## Docker

Generar un archivo docker que pueda ejecutar la API en forma contínua.
