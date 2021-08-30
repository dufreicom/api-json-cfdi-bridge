# Construcción de la imagen

Para construir la imagen de docker no es necesario todo el proyecto. Puedes seguir estos pasos:

```shell
mkdir -p /tmp/docker-api-json-cfdi-bridge/docker/
wget -O /tmp/docker-api-json-cfdi-bridge/Dockerfile \
    https://raw.githubusercontent.com/dufrei/api-json-cfdi-bridge/main/Dockerfile
wget -O /tmp/docker-api-json-cfdi-bridge/docker/supervisord.conf \
    https://raw.githubusercontent.com/dufrei/api-json-cfdi-bridge/main/docker/supervisord.conf
docker build -t api-json-cfdi-bridge /tmp/docker-api-json-cfdi-bridge/
rm -rf /tmp/docker-api-json-cfdi-bridge/
```

O si tienes clonado el proyecto simplemente:

```shell
docker build -t api-json-cfdi-bridge .
```

La construcción permite que especifiques los argumentos `GIT_REPO` y `GIT_BRANCH` para construir la imagen,
de esta forma podrás especificar otro repositorio y la rama con la que se creará la imagen, esto es muy útil
para hacer pruebas, por ejemplo:

```shell
docker build \
    --build-arg GIT_REPO="https://github.com/eclipxe13/api-json-cfdi-bridge.git"  \
    --build-arg GIT_BRANCH="feature/my-cool-feature" \
    -t api-json-cfdi-bridge:my-cool-feature .
```

También puedes desactivar totalmente la clonación del repositorio y usar los contenidos de la carpeta de construcción
pasando el valor `GIT_SOURCE=0`, por ejemplo:

```shell
docker build --build-arg GIT_SOURCE=0 -t api-json-cfdi-bridge:testing .
```
