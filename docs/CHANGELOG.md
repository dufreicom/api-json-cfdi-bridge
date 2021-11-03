# CHANGELOG

## SemVer 2.0

Utilizamos [Versionado Semántico 2.0.0](SEMVER.md).

## Versión 1.1.0

- La ruta `POST /build-cfdi-from-json` admite que los parámetros sean *archivos subidos* en lugar del
  *contenido de los archivos*, esto hace más fácil su uso y testeo.
- Se hacen diversas correcciones menores al código detectadas por PHPStan 1.0.1.
- En la documentación relacionada con la ejecución con variables de entorno, se agrega el argumento
  `php -d variables_order=EGPCS` a la línea de comandos para invocar al servidor de PHP.

## Versión 1.0.1

- Corrección de sintaxis en el archivo `Dockerfile`.
- Corregir el nombre del archivo de configuración de phpstan en `.gitattributes`.
- Actualización y corrección de estilo de `php-cs-fixer: 3.2.1`.

## Versión 1.0.0

- Versión inicial.
