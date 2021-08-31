# Actualización de los recursos SAT XML

Para actualizar el repositorio local de archivos SAT XML puede usar el *script* `bin/resource-sat-xml-download`.

Este comando requiere de la ruta donde deberá almacenar los recursos, con en el siguiente orden:

1. Del primer argumento de la línea de comandos, como: `bin/resource-sat-xml-download /var/sat-resources`.
2. De la variable de entorno `XMLRESOLVER_PATH`.
3. Si existe un archivo `.env` de la variable configurada en `XMLRESOLVER_PATH`.

Si no se encuenta, fallará la ejecución con un *exit code* con valor `1`.

La ruta se considera relativa al directorio desde donde se esté ejecutando el comando, o absoluta si así es la ruta.

En el procedimiento de descarga es necesario poder crear una carpeta temporal y permisos de escritura en el destino.

Ejemplo de ejecución:

```text
$ bin/resource-sat-xml-download build/resources
Downloading SAT XML resources to build/resources ... OK
```
