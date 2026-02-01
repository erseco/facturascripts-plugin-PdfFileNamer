# PdfFileNamer

Plugin para FacturaScripts 2025.x que permite personalizar los nombres de los archivos PDF generados para cada tipo de documento comercial.

## Funcionalidades

- Configura patrones personalizados para los nombres de archivos PDF
- Soporta diferentes patrones para cada tipo de documento:
  - Facturas de cliente
  - Facturas de proveedor
  - Presupuestos de cliente
  - Pedidos de cliente y proveedor
  - Albaranes de cliente y proveedor
- Tokens dinamicos que se reemplazan con datos del documento

## Requisitos

- FacturaScripts 2025.x o superior
- PHP 8.2 o superior

## Instalacion

1. Descarga el archivo ZIP del plugin
2. Ve a **Admin > Plugins** en FacturaScripts
3. Sube el archivo ZIP
4. Activa el plugin

## Configuracion

1. Ve a **Admin > Configuracion > Nombres de PDF**
2. Define los patrones para cada tipo de documento
3. Guarda los cambios

## Tokens disponibles

| Token | Descripcion |
|-------|-------------|
| `{code}` | Codigo del documento (ej: FAC2026-0001) |
| `{number}` | Numero del documento |
| `{serie}` | Codigo de serie |
| `{date}` | Fecha del documento (YYYY-MM-DD) |
| `{year}` | Ano de la fecha |
| `{month}` | Mes de la fecha (2 digitos) |
| `{day}` | Dia de la fecha (2 digitos) |
| `{company}` | Nombre corto de la empresa |
| `{company_cif}` | CIF/NIF de la empresa |
| `{customer}` | Razon social del cliente |
| `{customer_cif}` | CIF/NIF del cliente |
| `{supplier}` | Razon social del proveedor |
| `{supplier_cif}` | CIF/NIF del proveedor |
| `{doctype}` | Tipo de documento (ej: FacturaCliente) |

## Ejemplos de patrones

| Patron | Resultado |
|--------|-----------|
| `{company}_{code}` | MiEmpresa_FAC2026-0001 |
| `{year}/{month}/{code}_{customer}` | 2026_01_FAC2026-0001_ClienteX |
| `Factura_{code}` | Factura_FAC2026-0001 |
| `{doctype}_{number}_{date}` | FacturaCliente_1_2026-01-15 |

## Notas

- Si el patron esta vacio, se usara el nombre por defecto de FacturaScripts
- Los caracteres especiales se reemplazan automaticamente por guiones bajos
- El nombre del archivo se limita a 200 caracteres

## Desarrollo

```bash
# Iniciar contenedores Docker
make up

# Ejecutar tests
make test

# Verificar estilo de codigo
make lint

# Corregir estilo de codigo
make format

# Crear paquete ZIP
make package VERSION=1
```

## Licencia

LGPL v3. Ver archivo [LICENSE](LICENSE).

## Autor

Ernesto Serrano <info@ernesto.es>
