# HTTP API

## `POST /build-cfdi-from-json`

Datos enviados:

- `json`: JSON a convertir, firmar y estampar.
- `certificate`: Certificado a utilizar.
- `privatekey`: Llave privada del certificado, necesaria para firmar.
- `passphrase`: Clave de la llave privada.

Los datos enviados pueden ser el contenido mismo o bien un archivo (*file upload*).

Datos recibidos en caso correcto (`HTTP 200`):

- `converted`: XML convertido a partir del contenido JSON.
- `sourcestring`: Cadena de origen a firmar.
- `precfdi`: Contenido del CFDI firmado antes de estampar.
- `uuid`: UUID del CFDI estampado.
- `xml`: CFDI estampado.

Datos a recibir en caso de error (`HTTP 400`):

- `message`: Mensage de error
- `errors`: Arreglo de llave, mensajes.
  - `<llave>`: Dato relacionado al error.
  - `<mensajes>`: Cadena de texto con el error, un error por línea de texto.

Datos a recibir en caso de error de procesamiento (`HTTP 500`):

- `message`: Mensage de error
- `errors`: Arreglo de cadenas de texto con el mensaje de la excepción y las excepciones previas.
