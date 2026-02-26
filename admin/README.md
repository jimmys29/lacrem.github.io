# Sistema de Gestión de Clientes y Pedidos - La Crem

## 📋 Descripción

Sistema completo para gestionar clientes, crear pedidos y visualizar reportes de ventas. Permite:

✅ Crear y administrar clientes  
✅ Registrar pedidos vinculados a clientes  
✅ Ver reportes de ventas por cliente  
✅ Acumular cantidad de productos vendidos  
✅ Exportar datos a Excel  

---

## 🚀 Cómo Acceder

El panel de administración está disponible en:

```
http://tupagina.com/admin/
```

### Páginas disponibles:

1. **Panel Principal** (`/admin/index.html`)
   - Estadísticas generales
   - Acceso rápido a todas las funciones

2. **Gestionar Clientes** (`/admin/clientes.html`)
   - Crear nuevos clientes
   - Ver lista de clientes registrados
   - Eliminar clientes

3. **Crear Pedidos** (`/admin/pedidos.html`)
   - Seleccionar cliente
   - Agregar productos y cantidades
   - Calcular total con descuentos
   - Guardar pedido

4. **Reportes de Ventas** (`/admin/reportes.html`)
   - Resumen de ventas por cliente
   - Top 5 mejores clientes
   - Estadísticas generales
   - Exportar a Excel

---

## 📊 Estructura de Datos

### Clientes

```json
{
  "id": "cli_12345abc",
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "telefono": "+34 123 456 789",
  "direccion": "Calle Principal 123, Madrid",
  "empresa": "Empresa XYZ",
  "notas": "Cliente preferido",
  "fecha_creacion": "2024-02-25 10:30:00"
}
```

### Pedidos

```json
{
  "id": "ped_67890def",
  "cliente_id": "cli_12345abc",
  "items": [
    {
      "producto_id": "sig",
      "nombre": "Signature",
      "cantidad": 5,
      "precio": 150,
      "subtotal": 750
    }
  ],
  "descuento": 10,
  "notas": "Entrega rápida",
  "fecha": "2024-02-25 15:45:00"
}
```

### Resumen de Ventas

```json
{
  "cliente_id": "cli_12345abc",
  "cliente_nombre": "Juan Pérez",
  "cliente_email": "juan@example.com",
  "total_productos": 25,
  "total_ventas": 4500.00,
  "cantidad_pedidos": 8
}
```

---

## 🛠️ Archivos del Sistema

```
/admin/
├── index.html              # Panel principal
├── clientes.html           # Gestión de clientes
├── pedidos.html            # Crear pedidos
├── reportes.html           # Reportes y estadísticas
├── api/
│   ├── api.php             # API principal
│   └── db_functions.php    # Funciones de base de datos
├── data/
│   ├── clientes.json       # Datos de clientes
│   └── pedidos.json        # Datos de pedidos
└── js/
    └── (Scripts adicionales)
```

---

## 🔧 Funcionalidades

### ✨ Clientes

- **Crear**: Nombre, email, teléfono, dirección, empresa, notas
- **Listar**: Ver todos los clientes registrados
- **Eliminar**: Remover un cliente del sistema
- **Editar**: Funcionalidad próxima

### 📦 Pedidos

- **Crear**: Seleccionar cliente y agregar productos
- **Productos**: Signature, Premium, Tradicional, Refrescante, Elite
- **Cantidades**: Indicar cuántos de cada producto
- **Descuentos**: Aplicar descuentos porcentuales
- **Notas**: Agregar instrucciones especiales
- **Listar**: Ver últimos 10 pedidos registrados
- **Eliminar**: Remover un pedido

### 📈 Reportes

- **Estadísticas Generales**:
  - Total de clientes
  - Total de pedidos
  - Total de productos vendidos
  - Ingresos totales

- **Tabla Completa**: Resumen de todas las ventas por cliente
  - Categorías: Nuevo, VIP Plata, VIP Oro
  - Ordenadas por monto de ventas

- **Top 5 Clientes**: Mejores clientes clasificados

- **Exportar**: Descargar datos en formato CSV/Excel

---

## 💾 Almacenamiento

Actualmente, los datos se guardan en **archivos JSON**:
- `/admin/data/clientes.json`
- `/admin/data/pedidos.json`

Estos archivos se crean automáticamente en la primera ejecución.

### **Requisitos:**

1. La carpeta `/admin/data/` debe tener permisos de escritura (775 en Linux/Mac)
2. PHP debe estar habilitado en el servidor

---

## 🔄 Migración a MySQL (Futuro)

Cuando tengas acceso a una base de datos MySQL:

1. Proporciona los datos de conexión:
   - Host
   - Usuario
   - Contraseña
   - Nombre de BD

2. Se crearán las tablas:
   - `clientes`
   - `pedidos`
   - `pedido_items`

3. Se migrarán automáticamente los datos existentes

---

## 🆘 Solución de Problemas

### "No se guardan los datos"
- Verifica que `/admin/data/` exista y tenga permisos de escritura
- En servidor: `chmod 755 /admin/data/`

### "No aparecer el listado de clientes"
- Recarga la página (F5)
- Verifica la consola del navegador (F12) para ver errores

### "Los datos no aparecen en reportes"
- Asegúrate de haber creado al menos un cliente
- Asegúrate de haber creado un pedido vinculado a ese cliente

---

## 📱 Compatibilidad

✅ Funciona en todos los navegadores modernos  
✅ Responsive (adaptado para móviles)  
✅ Compatible con Chrome, Firefox, Safari, Edge  

---

## 🔐 Nota de Seguridad

Este sistema está diseñado para uso interno. Para un entorno de producción, se recomienda:

1. Agregar autenticación (login)
2. Validación más estricta de datos
3. Migrare a una base de datos SQL
4. Usar HTTPS
5. Implementar protección CSRF

---

## 📞 Soporte

Para preguntas o problemas, contacta al equipo de desarrollo.

---

**Última actualización:** Febrero 2024
**Versión:** 1.0
