# siroko-code-challenge
Desarrollo de una API de un carrito que posteriormente será consumido por la UI.

## Requerimientos

1. Gestión de productos eficientes: añadir, actualizar y eliminar productos del carrito.
2. Obtener el número total de productos del carrito.
3. Confirmar la compra del carrito.

## Estructura proyecto

- Se ha estructurado toda la lógica del proyecto dentro de la carpeta `src`, donde se ha dividido por carpetas que
corresponden a cada entidad, una carpeta para cosas compartidas entre las distintas clases `shared` y la carpeta de
 controladores `controller`.
- Dentro de cada carpeta de la entidad se ha dividido en 3 carpetas:
    - La carpeta `Application` donde se encuentran la lógica para cada uno de los distintos casos de uso.
    - La carpeta `Domain` donde se encuentra la lógica de dominio.
    - La carpeta `Infrastrcture` donde se encuentra la lógica en cuanto a conexiones externas como la base de datos.
- Además de la carpeta `src`, tenemos la carpeta `tests` para los tests unitarios y la carpeta `features` para los tests
funcionales.
